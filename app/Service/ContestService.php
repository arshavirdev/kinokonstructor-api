<?php

namespace App\Service;

use App\Models\Contest;
use Auth;

class ContestService
{
    public function favorite(Contest $contest)
    {
        $userId = Auth::id();
        $exists = $contest->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $contest->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $contest->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    public function archive(Contest $contest)
    {
        $contest->update(['is_archived' => true]);
        return ['is_archived' => true];
    }

    public function unarchive(Contest $contest)
    {
        $contest->update(['is_archived' => false]);
        return ['is_archived' => false];
    }
}
