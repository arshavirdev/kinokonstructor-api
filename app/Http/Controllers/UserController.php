<?php

namespace App\Http\Controllers;

use App\DTOs\MediaSyncDataDTO;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\StoreProfileSettingsRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateProfileSettingsRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Profile;
use App\Service\Media\MediaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ContactOrganizerRequest;

class UserController extends Controller
{
    public function __construct(private MediaService $mediaService)
    {

    }

    public function showCurrentUser()
    {
        $user = User::with(['profile.contact'])->find(Auth::id());
        return new UserResource($user);
    }

    private function syncMedia(Profile $profile, $params)
    {
        if (array_key_exists('avatar', $params)) {
            $profile->clearMediaCollection(Profile::AVATAR_MEDIA);
            if (is_null($params['avatar'])) {
                $profile->clearMediaCollection(Profile::AVATAR_MEDIA);
            } else {
                $profile->addMedia($params['avatar'])->toMediaCollection(Profile::AVATAR_MEDIA);
            }
        }

        if (array_key_exists('attachments', $params)) {
            $requestAttachments = collect($params['attachments']);
            $toSaveAttachments = $requestAttachments->filter(fn($item) => is_object($item));
            $toKeepAttachments = $requestAttachments->filter(fn($item) => !is_object($item))->map(fn($id) => ['id' => (int) $id]);
            $profile->clearMediaCollectionExcept(Profile::ATTACHMENT_MEDIA, $toKeepAttachments);
            foreach ($toSaveAttachments as $attachment) {
                $profile->addMedia($attachment)->toMediaCollection(Profile::ATTACHMENT_MEDIA);
            }
        }
    }

    private function syncRelations(Profile $profile, $params)
    {
        if (isset($params['username'])) {
            $user = $profile->user;
            $user->username = $params['username'];
            $user->save();
        }
        if (isset($params['education']))
            $profile->education()->sync($params['education']);

        if (isset($params['experience']))
            $profile->experience()->sync($params['experience']);

        if (isset($params['projects']))
            $profile->customProjects()->sync($params['projects']);

        if (isset($params['occupation_ids']))
            $profile->occupations()->sync($params['occupation_ids']);
    }

    public function createProfile(StoreProfileRequest $request)
    {
        $user = User::with('profile.contact')->find(Auth::id());

        if ($user->profile) {
            return response()->json(['message' => 'This user already has profile'], 400);
        }

        $params = $request->validated();
        $profile = $user->profile()->create($params);

        if (isset($params['username'])) {
            $user->username = $params['username'];
            $user->save();
        }

        $this->mediaService->syncMediaCollection($profile, new MediaSyncDataDTO(
            [$request->file(Profile::AVATAR_MEDIA)],
            [$request->post(Profile::AVATAR_MEDIA)]
        ), Profile::AVATAR_MEDIA);

        $this->syncRelations($profile, $params);

        // Handle contacts creation
        if (isset($params['contacts'])) {
            $contacts = $params['contacts'];

            $profile->contact()->create([
                'user_id' => $user->id,
                'phone' => $contacts['phone'] ?? [],
                'email' => $contacts['email'] ?? [],
                'website' => $contacts['website'] ?? [],
                'socials' => $contacts['socials'] ?? [],
                'other' => $contacts['other'] ?? [],
            ]);
        }

        $profile->putToModeration();
        $user->load('profile.contact');

        return new UserResource($user);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = User::with('profile.contact')->find(Auth::id());
        $profile = $user->profile;

        if (!$profile) {
            throw new ModelNotFoundException();
        }

        $params = $request->validated();

        $this->mediaService->syncMediaCollection($profile, new MediaSyncDataDTO(
            [$request->file(Profile::AVATAR_MEDIA)],
            [$request->post(Profile::AVATAR_MEDIA)]
        ), Profile::AVATAR_MEDIA);

        $profile->fill($params);
        $profile->save();

        if (isset($params['username'])) {
            $user->username = $params['username'];
            $user->save();
        }

        $this->syncRelations($profile, $params);

        // Handle contacts
        if (isset($params['contacts'])) {
            $contacts = $params['contacts'];
            $profile->contact()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $contacts['phone'] ?? [],
                    'email' => $contacts['email'] ?? [],
                    'website' => $contacts['website'] ?? [],
                    'socials' => $contacts['socials'] ?? [],
                    'other' => $contacts['other'] ?? [],
                ]
            );
        }

        $fields = collect(['firstname', 'lastname', 'middlename', 'birthday', 'gender', 'city', 'occupation_ids']);
        if ($fields->some(fn($field) => isset($params[$field])))
            $profile->putToModeration();

        $user->load('profile.contact');

        return new UserResource($user);
    }

    public function contactOrganizer(ContactOrganizerRequest $request)
    {
        $params = $request->validated();
        $profile = Profile::find($params['profile_id']);

        if (!$profile || !$profile->is_org) {
            return response()->json(['message' => 'The profile id is incorrect or it is not an organizer'], 400);
        }

        return response()->json([]);
    }

    public function deleteProfile()
    {
        $authUser = auth()->user();
        try {
            $authUser->delete();
            return response()->json(['message' => 'Profile successfully deleted'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function createProfileSettings(StoreProfileSettingsRequest $request)
    {
        $user = auth()->user();
        $profile = $user->profile;

        if (!$profile) {
            throw new ModelNotFoundException('Profile not found');
        }

        $params = $request->validated();

        if (isset($params['email'])) {
            $user->email = $params['email'];
            $user->save();
        }

        $profile->fill($params);
        $profile->save();

        $user->load('profile.contact');

        return new UserResource($user);
    }

    public function updateProfileSettings(UpdateProfileSettingsRequest $request)
    {
        $user = auth()->user();
        $profile = $user->profile;

        if (!$profile) {
            throw new ModelNotFoundException('Profile not found');
        }

        $params = $request->validated();
        if (isset($params['email'])) {
            $user->email = $params['email'];
            $user->save();
        }

        $profile->fill($params);
        $profile->save();

        $user->load('profile.contact');

        return new UserResource($user);
    }
}
