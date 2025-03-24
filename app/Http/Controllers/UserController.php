<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ContactOrganizerRequest;

class UserController extends Controller
{
    public function showCurrentUser()
    {
        $user = Auth::user();
        $user->load(['profile']);
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
            $toKeepAttachments = $requestAttachments->filter(fn($item) => !is_object($item))->map(fn($id) => ['id' => (int)$id]);
            $profile->clearMediaCollectionExcept(Profile::ATTACHMENT_MEDIA, $toKeepAttachments);
            foreach ($toSaveAttachments as $attachment) {
                $profile->addMedia($attachment)->toMediaCollection(Profile::ATTACHMENT_MEDIA);
            }
        }
    }

    private function syncRelations(Profile $profile, $params)
    {
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
        $user = Auth::user();

        $params = $request->validated();
        $profile = $user->profile()->create($params);

        $this->syncMedia($profile, $params);
        $this->syncRelations($profile, $params);

        $profile->putToModeration();

        return [];
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $profile = Auth::user()->profile;
        if (!$profile) throw new ModelNotFoundException();

        $params = $request->validated();

        $this->syncMedia($profile, $params);

        $profile->fill($params);
        $profile->save();

        $this->syncRelations($profile, $params);

        $fields = collect(['firstname', 'lastname', 'middlename', 'birthday', 'gender', 'city', 'occupation_ids']);
        if ($fields->some(fn($field) => isset($params[$field])))
            $profile->putToModeration();

        return [];
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
}
