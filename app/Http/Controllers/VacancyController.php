<?php

namespace App\Http\Controllers;

use App\Http\Requests\VacancyRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use App\Service\VacancyService;
use Symfony\Component\HttpFoundation\Request;

class VacancyController extends Controller
{
    function __construct(private VacancyService $vacancyService)
    {
    }

    /**
     * Display a listing of the vacancy.
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user?->id;
        $profileId = $user?->profile?->id;

        $query = Vacancy::query()->with(['owner'])
            ->withCount([
                'favorites as is_favorite' => fn($q) => $q->where('user_id', $userId),
            ]);

        $query->when($request->get('type') === 'my', function ($q) use ($profileId) {
            $q->where('owner_id', $profileId);
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where('title', 'like', '%' . $request->get('search') . '%');
        });

        $query->when(filter_var($request->input('favorite'), FILTER_VALIDATE_BOOLEAN), function ($q) use ($profileId) {
            $q->whereHas('favorites', fn($subQ) => $subQ->where('owner_id', $profileId));
        });

        $query->when($request->filled('region_id'), function ($q) use ($request) {
            $q->where('region_id', $request->input('region_id'));
        });

        $query->when($request->filled('experience'), function ($q) use ($request) {
            $q->where('experience', $request->input('experience'));
        });

        $query->when($request->filled('format'), function ($q) use ($request) {
            $q->where('format', $request->input('format'));
        });

        $resources = $query->orderBy('created_at', 'DESC')
            ->paginate($request->input('pageSize', 10));
        return VacancyResource::collection($resources);
    }

    /**
     * Store a newly created vacancy in storage.
     *
     * @param  VacancyRequest  $request
     * @return VacancyResource
     */
    public function store(VacancyRequest $request)
    {
        $vacancy = $this->vacancyService->store($request);
        return new VacancyResource($vacancy, true);
    }

    /**
     * Display the specified vacancy.
     *
     * @param  \App\Models\Vacancy  $vacancy
     * @return VacancyResource
     */
    public function show(Vacancy $vacancy)
    {
        return new VacancyResource($vacancy, true);
    }

    /**
     * Update the specified vacancy in storage.
     *
     * @param  VacancyRequest  $request
     * @param  \App\Models\Vacancy  $vacancy
     * @return VacancyResource
     */
    public function update(VacancyRequest $request, Vacancy $vacancy)
    {
        $vacancy = $this->vacancyService->update($vacancy, $request);
        return new VacancyResource($vacancy, true);
    }

    /**
     * Remove the specified vacancy from storage.
     *
     * @param  \App\Models\Vacancy  $vacancy
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vacancy $vacancy)
    {
        $this->vacancyService->delete($vacancy);
        return response()->json(['success' => true]);
    }

    public function action(Vacancy $vacancy, string $action)
    {
        $result = match ($action) {
            'favorite' => $this->vacancyService->favorite($vacancy),
            'archive' => $this->vacancyService->archive($vacancy),
            'unarchive' => $this->vacancyService->unarchive($vacancy),
            default => ['error' => 'Invalid action']
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
