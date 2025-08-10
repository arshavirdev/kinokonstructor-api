<?php

namespace App\Traits;

trait Archivable
{
    public function scopeVisibleTo($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // Everyone sees non-archived contests
            $q->where('is_archived', false);

            // TODO: check
            // Owners see their own archived contests
            // if ($user && $user->profile?->id) {
            //     $q->orWhere(function ($q2) use ($user) {
            //         $q2->where('is_archived', true)
            //             ->where('owner_id', $user->profile->id);
            //     });
            // }
        });
    }
}