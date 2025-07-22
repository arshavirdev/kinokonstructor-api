<?php

namespace App\Service;

use App\DTOs\MediaSyncDataDTO;
use App\Http\Requests\StoreContestApplicationRequest;
use App\Http\Resources\ContestApplicationsResource;
use Auth;
use App\Notifications\NewContestApplicationNotification;
use App\Models\ContestApplication;
use App\Models\Contest;
use App\Service\Media\MediaService;

class ContestService
{
    public function __construct(private MediaService $mediaService) {}

    public function favorite(Contest $contest)
    {
        $userId = Auth::id();
        $exists = $contest->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $contest->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $contest->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    public function archive(Contest $contest)
    {
        $contest->update(['is_archived' => true]);
        return ['is_archived' => true];
    }

    public function unarchive(Contest $contest)
    {
        $contest->update(['is_archived' => false]);
        return ['is_archived' => false];
    }

    public function apply(Contest $contest, StoreContestApplicationRequest $request)
    {
        $authUser = auth()->user();
        $profileId = $authUser->profile->id;

        // $exists = ContestApplication::where('contest_id', $contest->id)
        //     ->where('applicant_id', $profileId)
        //     ->first();

        // if ($exists) {
        //     return [
        //         'success' => false,
        //         'error' => 'You have already applied to this contest.',
        //     ];
        // }

        $application = ContestApplication::create([
            'applicant_id' => $profileId,
            'contest_id' => $contest->id,
            ...$request->validated()
        ]);

        $filesMediaDto = new MediaSyncDataDTO(
            $request->file('files_section', []),
            $request->post('files_section', [])
        );

        $this->handleFileUploads($application, $filesMediaDto);

        $imagesMediaDto = new MediaSyncDataDTO(
            $request->file('images_files', []),
            $request->post('images_files', [])
        );

        $this->mediaService->syncMediaCollection($application, $imagesMediaDto, ContestApplication::CONTEST_APPLICATION_IMAGES);

        $application->load(['media']);

        // Send application notification
        $notification = new NewContestApplicationNotification($application);
        $contest->owner->user->notify($notification);

        return [
            'success' => true,
            'data' => new ContestApplicationsResource($application)
        ];
    }

    private function handleFileUploads(ContestApplication $application, MediaSyncDataDTO $mediaDto): void
    {
        foreach ($mediaDto->files as $groupIndex => $group) {

            $postData = $mediaDto->post[$groupIndex] ?? [];
            $sectionMediaDto = new MediaSyncDataDTO(
                $group['files'],
                $postData
            );
            $this->mediaService->syncMediaCollection(
                $application,
                $sectionMediaDto,
                ContestApplication::CONTEST_APPLICATION_FILES
            );
        }
    }
}
