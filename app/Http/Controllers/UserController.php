<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $user->load(['profile']);
        return new UserResource($user);
    }

    public function createProfile(StoreProfileRequest $request)
    {
        $user = Auth::user();

        $params = $request->validated();
        $profile = $user->profile()->create($params);

        if (isset($params['avatar'])) {
            $profile->clearMediaCollection(Profile::AVATAR_MEDIA);
            $profile->addMedia($params['avatar'])->toMediaCollection(Profile::AVATAR_MEDIA);
        }

        if (isset($params['attachments'])) {
            $profile->clearMediaCollection(Profile::ATTACHMENT_MEDIA);
            $profile->addMedia($params['attachments'])->toMediaCollection(Profile::ATTACHMENT_MEDIA);
        }

        if (isset($params['education']))
            $profile->education()->sync($params['education']);

        if (isset($params['experience']))
            $profile->experience()->sync($params['experience']);

        if (isset($params['projects']))
            $profile->customProjects()->sync($params['projects']);


        $profile = Profile::with(['experience', 'education', 'customProjects', 'projects', 'media', 'occupation'])->find($profile->id);
        return $profile;
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $profile = Auth::user()->profile;
        if (!$profile) throw new ModelNotFoundException();

        $params = $request->validated();

        if (isset($params['avatar'])) {
            $profile->addMedia($params['avatar'])->toMediaCollection(Profile::AVATAR_MEDIA);
        }

        if (isset($params['attachments'])) {
            $profile->addMedia($params['attachments'])->toMediaCollection(Profile::ATTACHMENT_MEDIA);
        }

        $profile->fill($params);
        $profile->save();

        if (isset($params['education']))
            $profile->education()->sync($params['education']);

        if (isset($params['experience']))
            $profile->experience()->sync($params['experience']);

        if (isset($params['projects']))
            $profile->customProjects()->sync($params['projects']);

        return [];
    }

}
