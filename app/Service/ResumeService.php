<?php

namespace App\Service;

use App\Models\Resume;
use App\Http\Requests\StoreResumeRequest;
use App\Service\Shared\ContactHandlerService;
use Illuminate\Support\Facades\Auth;

class ResumeService
{
    function __construct(private ContactHandlerService $contactHandlerService)
    {
    }

    public function store(StoreResumeRequest $request): Resume
    {
        $authUser = auth()->user();
        $data = $request->validated();
        $data['owner_id'] = $authUser->profile->id;

        $resume = Resume::create($data);

        if ($request->has('contacts') && isset($data['contacts'])) {
            $this->contactHandlerService->handle($resume, $data['contacts'], $authUser);
        }

        $resume->load('contacts');
        return $resume;
    }

    public function update(Resume $resume, StoreResumeRequest $request): Resume
    {
        $data = $request->validated();
        $authUser = auth()->user();
        $resume->update($data);

        if ($request->has('contacts') && isset($data['contacts'])) {
            $this->contactHandlerService->handle($resume, $data['contacts'], $authUser);
        }

        $resume->load('contacts');
        return $resume;
    }

    public function delete(Resume $resume): bool
    {
        return $resume->delete();
    }

    /**
     * Favorite/Unfavorite
     * @param \App\Models\Resume $resume
     * @return array{is_favorite: bool}
     */
    public function favorite(Resume $resume)
    {
        $userId = Auth::id();
        $exists = $resume->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $resume->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $resume->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    /**
     * Archive
     * @param \App\Models\Resume $resume
     * @return array{is_archived: bool}
     */
    public function archive(Resume $resume)
    {
        if ($resume->update(['is_archived' => true])) {
            return ['is_archived' => true];
        }

        return ['is_archived' => false];
    }

    /**
     * Unarchive
     * @param \App\Models\Resume $resume
     * @return array{is_archived: bool}
     */
    public function unarchive(Resume $resume)
    {
        if ($resume->update(['is_archived' => false])) {
            return ['is_archived' => false];
        }

        return ['is_archived' => true];
    }
}
