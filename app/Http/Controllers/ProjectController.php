<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequestNEW;
use App\Http\Requests\UpdateProjectRequestNEW;
use App\Http\Resources\ProjectBriefResource;
use App\Http\Resources\ProjectLocationResource;
use App\Http\Resources\ProjectResource;

use Auth;
use App\Service\ProjectService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        $query = Project::query()->with(['media'])
            ->withCount([
                'favorites as is_favorite' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ]);

        if ($request->has('type')) {
            $type = $request->get('type');
            if (!$profile && ($type === 'my' || $type === 'membership'))
                return abort(404);

            if ($type === 'all' || !$type) {
                $query = $query->where('status', 'accepted')->onlyAccepted();
            } elseif ($type === 'my') {
                $query = $query->where('owner_id', $profile->id)->orderBy('updated_at', 'DESC');
            } elseif ($type === 'membership') {
                $query = $query->whereHas('memberInvites', function (Builder $query) use ($profile) {
                    $query->where('profile_id', $profile->id);
                });
            } elseif ($type === 'moderation') {
                $query = $query->where('status', 'moderation')->orderBy('updated_at', 'ASC');
            }
        }

        if ($request->has('filter')) {
            if ($request->has('filter.date')) {
                $query = $query->whereBetween('start_date', [$request->input('filter.date')[0], $request->input('filter.date')[1]]);
            }

            if ($request->has('filter.genre')) {
                $query = $query->where('genres', 'like', '%' . $request->input('filter.genre') . '%');
            }

            if ($request->has('filter.format')) {
                $query = $query->where('format', $request->input('filter.format'));
            }

            if ($request->has('filter.location')) {
                $regionId = $request->input('filter.location');
                $query = $query->where('region_id', $regionId);
//                $query = $query->whereHas('locations', function ($q) use ($locationFilter) {
//                    $q->whereIn('locations.id', (array) $locationFilter);
//                });
            }

            if ($request->has('filter.requests')) {
                $filterRequests = $request->input('filter.requests');
                $query = $query->whereHas('requests', function ($q) use ($filterRequests) {
                    $q->whereIn('requests.type', $filterRequests);
                });
            }
        }

        if ($request->has('title'))
            $query = $query->where('title', 'like', '%' . $request->input('title') . '%');

        if ($request->has('format'))
            $query = $query->where('format', $request->input('format'));

        if ($request->has('genre_type'))
            $query = $query->where('genre_type', $request->input('genre_type'));

        $projects = $query->orderBy('created_at', 'desc')->paginate($request->input('pageSize', 10));
        return ProjectBriefResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequestNEW $request
     * @param ProjectService $projectService
     * @return JsonResponse|ProjectResource
     */
    public function store(StoreProjectRequestNEW $request, ProjectService $projectService): JsonResponse|ProjectResource
    {
        $request->validated();

        try {
            $project = DB::transaction(function () use ($request, $projectService) {
                return $projectService->store($request);
            });

            return new ProjectResource($project);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        //        if ($project->owner_id !== Auth::user()->profile->id || $project->status !== Status::ACCEPTED) abort(403);

        $project->load(['media', 'memberInvites', 'memberInvites.profile', 'locations', 'owner']);

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProjectRequest $request
     * @param ProjectService $projectService
     * @return ProjectResource|JsonResponse
     */
    public function update(Project $project, UpdateProjectRequestNEW $request, ProjectService $projectService): ProjectResource|JsonResponse
    {
        try {
            $project = DB::transaction(function () use ($request, $projectService, $project) {
                return $projectService->update($request, $project);
            });

            return new ProjectResource($project);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function moderate(Project $project): array
    {
        if ($project->owner_id !== Auth::user()->profile->id)
            abort(403);

        $project->putToModeration();
        return [];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        //
    }

    private function syncMedia(Project $project, $params)
    {
        $singleMedias = [
            Project::EXTENDED_SYNOPSIS_MEDIA,
            Project::COSTUMES_MEDIA,
            Project::MAKEUP_MEDIA,
            Project::CAST_MEDIA,
            Project::DECORATIONS_MEDIA,
            Project::LOCATIONS_MEDIA,
            Project::FINANCIAL_PLAN_MEDIA,
            Project::FINANCIAL_PROOF_MEDIA,
            Project::PARTNERSHIP_PROOF_MEDIA
        ];
        $medias = [Project::ATTACHMENTS_MEDIA];
        foreach ($singleMedias as $key) {
            if (!array_key_exists($key, $params))
                continue;
            if (is_null($params[$key])) {
                $project->clearMediaCollection($key);
            } elseif (is_object($params[$key])) {
                $project->clearMediaCollection($key);
                $project->addMedia($params[$key])->toMediaCollection($key);
            } else {
            }
        }
        foreach ($medias as $key) {
            if (!array_key_exists($key, $params))
                continue;
            $requestItems = collect($params[$key]);
            $toSaveItems = $requestItems->filter(fn($item) => is_object($item));
            $toKeepItems = $requestItems->filter(fn($item) => !is_object($item))->map(fn($id) => ['id' => (int) $id]);
            $project->clearMediaCollectionExcept($key, $toKeepItems);
            foreach ($toSaveItems as $attachment) {
                $project->addMedia($attachment)->toMediaCollection($key);
            }
        }
    }

    private function syncRelations(Project $project, $params) {}

    public function indexLocations(Project $project)
    {
        return ProjectLocationResource::collection($project->locations);
    }

    public function addLocation(Project $project, Request $request)
    {
        $locationIds = $request->input('locationIds', []);
        $project->locations()->attach($locationIds);
    }

    public function removeLocation(Project $project, Request $request)
    {
        $locationIds = $request->input('locationIds', []);
        $project->locations()->detach($locationIds);
    }

    public function cancelModeration(Project $project)
    {
        if ($project->owner_id !== Auth::user()->profile->id)
            abort(403);
        $project->cancelModeration();
    }

    public function approveModeration(Project $project)
    {
        $project->markAccepted();
    }

    public function rejectModeration(Project $project, Request $request)
    {
        $project->markRejected($request->input('comment'), $request->except('comment'));
    }

    public function action(Project $project, string $action, ProjectService $projectService)
    {
        // Allowed actions
        $allowedActions = ['favorite', 'archive', 'unarchive'];

        if (!in_array($action, $allowedActions)) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        if (in_array($action, ['archive', 'unarchive'])) {
            $this->authorize($action, $project);
        }

        $result = match ($action) {
            'favorite' => $projectService->favorite($project),
            'archive' => $projectService->archive($project),
            'unarchive' => $projectService->unarchive($project),
            default => response()->json(['message' => 'Invalid action'], 400)
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
