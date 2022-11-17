<?php

namespace App\Http\Controllers\Admin;

use \App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $users = User::query()->with(['profile']);
        if ($request->has('type')) {
            if ($request->input('type') === 'moderation')
                $users = $users->whereHas('profile', function (Builder $query) {
                    $query->where('status', 'moderation');
                });
        }
        return UserResource::collection($users->paginate());
    }

}
