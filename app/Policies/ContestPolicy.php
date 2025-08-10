<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Contest;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contest  $contest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Contest $contest)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contest  $contest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Contest $contest)
    {
        return $user?->profile?->id === $contest->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contest  $contest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Contest $contest)
    {
        return $user?->profile?->id === $contest->owner_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contest  $contest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Contest $contest)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contest  $contest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Contest $contest)
    {
        //
    }

    /**
     * Determine whether the user can archive the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contest  $contest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function archive(User $user, Contest $contest)
    {
        return $user->id === $contest->owner->user->id;
    }

    /**
     * Determine whether the user can archive the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contest  $contest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function unarchive(User $user, Contest $contest)
    {
        return $user->id === $contest->owner->user->id;
    }
}
