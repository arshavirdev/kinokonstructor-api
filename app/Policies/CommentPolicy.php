<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id
            || $user->hasRole('admin')
            || $user->hasRole('moderator');
    }

    public function hide(User $user, Comment $comment): bool
    {
        return $user->hasRole('admin') || $user->hasRole('moderator');
    }
}
