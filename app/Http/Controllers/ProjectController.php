<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;

use App\Models\Profile;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;

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

            if ($type === 'all') {
                $query = $query->where('status', 'accepted');
            } elseif ($type === 'my') {
                $query = $query->where('owner_id', $profile->id);
            } elseif ($type === 'membership') {
                $query = $query->whereHas('member', function (Builder $query) use ($profile) {
                    $query->where('profile_id', $profile->id);
                });
            } elseif ($type === 'moderation') {
                $query = $query->where('status', 'moderation');
            }
        }

        if ($request->has('name'))
            $query = $query->where('name', 'ilike', $request->input('name'));

        if ($request->has('format'))
            $query = $query->where('format', $request->input('format'));

        if ($request->has('genre_type'))
            $query = $query->where('genre_type', $request->input('genre_type'));

        // if ($request->has('city'))
        //     $query = $query->where('city', 'ilike', $request->input('city'));

        $projects = $query->paginate();
        return ProjectResource::collection($projects);
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
        $project->load(['media']);
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

        $project->status = 'moderation';
        $project->save();
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
}
