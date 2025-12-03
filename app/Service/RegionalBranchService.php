<?php

namespace App\Service;

use App\DTOs\MediaSyncDataDTO;
use App\Http\Requests\RegionalBranch\RegionalBranchRequest;
use App\Models\RegionalBranch;
use App\Service\Media\MediaService;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegionalBranchService
{
    function __construct(private MediaService $mediaService)
    {

    }

    public function store(RegionalBranchRequest $request): ?RegionalBranch
    {
        $authUser = auth()->user();
        $insertData = $request->validated();
        $insertData['owner_id'] = $authUser->profile->id;

        // $this->checkRegionExisting($insertData['region_id']);

        $branch = RegionalBranch::create($insertData);

        $mediaDataDto = new MediaSyncDataDTO(
            $request->file(RegionalBranch::DOCS_FILES, []),
            $request->post(RegionalBranch::DOCS_FILES, [])
        );

        $this->mediaService->syncMediaCollection($branch, $mediaDataDto, RegionalBranch::DOCS_FILES);

        if ($request->has('contacts')) {
            $this->handleContacts($branch, $request->input('contacts'), $authUser);
        }

        // Reload the relation to access media, news and member
        $branch->load(['members', 'news', 'media', 'contacts']);

        return $branch;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(RegionalBranch $branch, RegionalBranchRequest $request): ?RegionalBranch
    {
        $authUser = auth()->user();
        $branch->update($request->validated());

        $mediaDataDto = new MediaSyncDataDTO(
            $request->file(RegionalBranch::DOCS_FILES, []),
            $request->post(RegionalBranch::DOCS_FILES, [])
        );

        $this->mediaService->syncMediaCollection($branch, $mediaDataDto, RegionalBranch::DOCS_FILES);

        if ($request->has('contacts')) {
            $this->handleContacts($branch, $request->input('contacts'), $authUser);
        }

        // Reload the relation to access media
        $branch->load(['members', 'news', 'media', 'contacts']);

        return $branch;
    }
    private function handleContacts(RegionalBranch $branch, $contacts, $user): void
    {
        $data = [
            'phone' => $contacts['phone'] ?? [],
            'email' => $contacts['email'] ?? [],
            'website' => $contacts['website'] ?? [],
            'socials' => $contacts['socials'] ?? [],
            'other' => $contacts['other'] ?? [],
        ];

        if ($branch->contacts->count()) {
            $branch->contacts()->update($data);
            return;
        }

        $branch->contacts()->create(array_merge(['user_id' => $user->id], $data));
    }

    /**
     * Check if regional branch already exist by region ID
     * @param int $regionId
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @return void
     */
    private function checkRegionExisting(int $regionId)
    {
        $isExist = RegionalBranch::where('region_id', $regionId)->exists();
        if ($isExist) {
            throw new HttpException(409, 'Regional branch already exists');
        }
    }
}
