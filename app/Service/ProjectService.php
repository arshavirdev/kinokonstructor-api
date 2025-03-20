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
            return ['error' => 'Project already favorited'];
        }

        $project->favorites()->create(['user_id' => $userId]);

        return ['message' => 'Project favorited successfully'];
    }

    public function unfavorite(Project $project)
    {
        $userId = Auth::id();
        $deleted = $project->favorites()->where('user_id', $userId)->delete();

        if (!$deleted) {
            return ['error' => 'Project was not favorited'];
        }

        return ['message' => 'Project unfavorited successfully'];
    }

    public function archive(Project $project)
    {
        $project->update(['is_archived' => true]);
        return ['message' => 'Project archived'];
    }

    public function unarchive(Project $project)
    {
        $project->update(['is_archived' => false]);
        return ['message' => 'Project unarchived'];
    }
}
