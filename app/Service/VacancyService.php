<?php

namespace App\Service;

use App\Models\Vacancy;
use App\Http\Requests\VacancyRequest;
use App\Service\Shared\ContactHandlerService;
use Illuminate\Support\Facades\Auth;

class VacancyService
{
    function __construct(private ContactHandlerService $contactHandlerService)
    {
    }

    public function store(VacancyRequest $request): Vacancy
    {
        $authUser = auth()->user();
        $data = $request->validated();
        $data['owner_id'] = $authUser->profile->id;

        $vacancy = Vacancy::create($data);

        if ($request->has('contacts')) {
            $this->contactHandlerService->handle($vacancy, $data['contacts'], $authUser);
        }

        $vacancy->load('contacts');
        return $vacancy;
    }

    public function update(Vacancy $vacancy, VacancyRequest $request): Vacancy
    {
        $data = $request->validated();
        $authUser = auth()->user();
        $vacancy->update($data);

        if ($request->has('contacts')) {
            $this->contactHandlerService->handle($vacancy, $data['contacts'], $authUser);
        }

        $vacancy->load('contacts');
        return $vacancy;
    }

    public function delete(Vacancy $vacancy): bool
    {
        return $vacancy->delete();
    }

    /**
     * Favorite/Unfavorite
     * @param \App\Models\Vacancy $vacancy
     * @return array{is_favorite: bool}
     */
    public function favorite(Vacancy $vacancy)
    {
        $userId = Auth::id();
        $exists = $vacancy->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $vacancy->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $vacancy->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    /**
     * Archive
     * @param \App\Models\Vacancy $vacancy
     * @return array{is_archived: bool}
     */
    public function archive(Vacancy $vacancy)
    {
        if ($vacancy->update(['is_archived' => true])) {
            return ['is_archived' => true];
        }

        return ['is_archived' => false];
    }

    /**
     * Unarchive
     * @param \App\Models\Vacancy $vacancy
     * @return array{is_archived: bool}
     */
    public function unarchive(Vacancy $vacancy)
    {
        if ($vacancy->update(['is_archived' => false])) {
            return ['is_archived' => false];
        }

        return ['is_archived' => true];
    }
}
