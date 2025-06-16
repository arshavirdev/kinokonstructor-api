<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResumeRequest;
use App\Http\Resources\ResumeResource;
use App\Models\Resume;
use App\Service\ResumeService;
use Symfony\Component\HttpFoundation\Request;

class ResumeController extends Controller
{
    function __construct(private ResumeService $resumeService)
    {
    }

    /**
     * Display a listing of the resume.
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user?->id;
        $profileId = $user?->profile?->id;

        $query = Resume::query()->with(['owner'])
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
        return ResumeResource::collection($resources);
    }

    /**
     * Store a newly created resume in storage.
     *
     * @param  StoreResumeRequest  $request
     * @return ResumeResource
     */
    public function store(StoreResumeRequest $request)
    {
        $resume = $this->resumeService->store($request);
        return new ResumeResource($resume);
    }

    /**
     * Display the specified resume.
     *
     * @param  \App\Models\Resume  $resume
     * @return ResumeResource
     */
    public function show(Resume $resume)
    {
        return new ResumeResource($resume);
    }

    /**
     * Update the specified resume in storage.
     *
     * @param  StoreResumeRequest  $request
     * @param  \App\Models\Resume  $resume
     * @return ResumeResource
     */
    public function update(StoreResumeRequest $request, Resume $resume)
    {
        $this->authorize('update', $resume);
        $resume = $this->resumeService->update($resume, $request);
        return new ResumeResource($resume);
    }

    /**
     * Remove the specified resume from storage.
     *
     * @param  \App\Models\Resume  $resume
     * @return \Illuminate\Http\Response
     */
    public function destroy(Resume $resume)
    {
        $this->authorize('delete', $resume);
        $this->resumeService->delete($resume);
        return response()->json(['success' => true]);
    }

    public function action(Resume $resume, string $action)
    {
        $result = match ($action) {
            'favorite' => $this->resumeService->favorite($resume),
            'archive' => $this->resumeService->archive($resume),
            'unarchive' => $this->resumeService->unarchive($resume),
            default => ['error' => 'Invalid action']
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
