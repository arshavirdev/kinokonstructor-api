<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectBriefResource;
use App\Http\Resources\ProjectLocationResource;
use App\Http\Resources\ProjectResource;

use App\Traits\Moderation\Status;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Location;

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
        $query = Project::query()->with(['media']);

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

        if ($request->has('title'))
            $query = $query->where('title', 'ilike', $request->input('title'));

        if ($request->has('format'))
            $query = $query->where('format', $request->input('format'));

        if ($request->has('genre_type'))
            $query = $query->where('genre_type', $request->input('genre_type'));

        $projects = $query->paginate($request->input('pageSize', 10));
        return ProjectBriefResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreProjectRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        $projectData = $request->validated();
        $projectData['owner_id'] = Auth::user()->profile->id;
        $project = Project::create($projectData);

        $this->syncMedia($project, $projectData);
        $this->syncRelations($project, $projectData);

        return new ProjectResource($project);
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

        $project->load(['media', 'memberInvites', 'memberInvites.profile', 'locations']);

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateProjectRequest $request
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $params = $request->validated();

        $this->syncMedia($project, $params);
        $project->fill($params);
        $project->save();

        $this->syncRelations($project, $params);

        return [];
    }

    public function moderate(Project $project): array
    {
        if ($project->owner_id !== Auth::user()->profile->id) abort(403);

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
            if (!array_key_exists($key, $params)) continue;
            if (is_null($params[$key])) {
                $project->clearMediaCollection($key);
            } elseif (is_object($params[$key])) {
                $project->clearMediaCollection($key);
                $project->addMedia($params[$key])->toMediaCollection($key);
            } else {

            }
        }
        foreach ($medias as $key) {
            if (!array_key_exists($key, $params)) continue;
            $requestItems = collect($params[$key]);
            $toSaveItems = $requestItems->filter(fn($item) => is_object($item));
            $toKeepItems = $requestItems->filter(fn($item) => !is_object($item))->map(fn($id) => ['id' => (int)$id]);
            $project->clearMediaCollectionExcept($key, $toKeepItems);
            foreach ($toSaveItems as $attachment) {
                $project->addMedia($attachment)->toMediaCollection($key);
            }
        }
    }

    private function syncRelations(Project $project, $params)
    {
    }

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
        if ($project->owner_id !== Auth::user()->profile->id) abort(403);
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
}
