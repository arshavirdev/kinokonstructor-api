<?php

namespace App\Service;

use App\Models\Project;
use Auth;

class ProjectService
{
    public function favorite(Project $project)
    {
        $userId = Auth::id();
        $exists = $project->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            return ['is_favorite' => true];
        }

        $project->favorites()->create(['user_id' => $userId]);

        return ['is_favorite' => false];
    }

    public function unfavorite(Project $project)
    {
        $userId = Auth::id();
        $deleted = $project->favorites()->where('user_id', $userId)->delete();

        if (!$deleted) {
            return ['is_favorite' => false];
        }

        return ['is_favorite' => false];
    }

    public function archive(Project $project)
    {
        $project->update(['is_archived' => true]);
        return ['is_archived' => true];
    }

    public function unarchive(Project $project)
    {
        $project->update(['is_archived' => false]);
        return ['is_archived' => false];
    }
}
