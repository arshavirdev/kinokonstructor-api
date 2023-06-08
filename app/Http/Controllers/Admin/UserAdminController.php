<?php

namespace App\Http\Controllers\Admin;

use \App\Http\Controllers\Controller;
use App\Http\Resources\UserByProfileResource;
use App\Http\Resources\UserBriefResource;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function markAsVerified(User $user)
    {
        $profile = $user->profile;
        $profile->is_verified = true;
        $profile->save();
    }

    public function markAsUnverified(User $user)
    {
        $profile = $user->profile;
        $profile->is_verified = false;
        $profile->save();
    }

    public function setRole(User $user, Request $request)
    {
        $user->role = $request->input('role');
        $user->save();
    }

    public function approveProfile(Profile $profile)
    {
        $profile->markAccepted();
        $user = $profile->user;
        if ($user->role === 'guest') {
            $user->role = 'specialist';
            $user->save();
        }
    }

    public function rejectProfile(Profile $profile, Request $request)
    {
        $comment = $request->input('comment');
        $profile->markRejected($comment);
        $profile->status = 'rejected';
        $profile->save();
    }

    public function index(Request $request)
    {
        if ($request->has('type') && $request->input('type') === 'moderation')
            return $this->moderationIndex($request);

        return $this->profileIndex($request);
    }

    public function moderationIndex(Request $request)
    {
        $users = User::query()->with(['profile', 'profile.occupations', 'profile.media'])->orderByDesc('updated_at');

        $users = $users->whereHas('profile', function (Builder $query) {
            $query->where('status', 'moderation');
        });

        if ($request->has('fullname')) {
            $users = $users->whereHas('profile', function (Builder $query) use ($request) {
                $query->whereFullname($request->input('fullname'));
            });
        }
        if ($request->has('email')) {
            $users = $users->where('email', 'ILIKE', '%' . trim($request->input('email')) . '%');
        }
        return UserBriefResource::collection($users->paginate());
    }

    public function profileIndex(Request $request)
    {
        $profiles = Profile::query()->with(['user', 'occupations', 'media'])->orderByDesc('updated_at');

        if ($request->has('fullname')) {
            $profiles = $profiles->whereFullname($request->input('fullname'));
        }
        if ($request->has('email')) {
            $profiles = $profiles->whereHas('user', function (Builder $query) use ($request) {
                $query->where('email', 'ILIKE', '%' . trim($request->input('email')) . '%');
            });
        }
        return UserByProfileResource::collection($profiles->paginate(50));
    }

    public function destroy(User $user)
    {
        $user->delete();
    }
}
