<?php

namespace App\Service;

use App\Http\Requests\StoreContestApplicationRequest;
use Auth;
use App\Models\ContestApplication;
use App\Models\Contest;

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

    public function apply(Contest $contest, StoreContestApplicationRequest $request)
    {
        $authUser = auth()->user();
        $profileId = $authUser->profile->id;

        $exists = ContestApplication::where('contest_id', $contest->id)
            ->where('applicant_id', $profileId)
            ->first();

        if ($exists) {
            return [
                'success' => false,
                'error' => 'You have already applied to this contest.',
            ];
        }

        $application = ContestApplication::create([
            'applicant_id' => $profileId,
            'contest_id' => $contest->id,
            'project_id' => $request->get('project_id')
        ]);

        return [
            'success' => true,
            'data' => $application
        ];
    }
}
