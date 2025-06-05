<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use App\Service\ResourceService;
use Symfony\Component\HttpFoundation\Request;

class ResourceController extends Controller
{
    function __construct(private ResourceService $resourceService)
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user?->id;
        $profileId = $user?->profile?->id;

        $query = Resource::query()->with(['owner'])
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

        $resources = $query->orderBy('created_at', 'DESC')
            ->paginate($request->input('pageSize', 10));
        return ResourceResource::collection($resources);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ResourceRequest  $request
     * @return ResourceResource
     */
    public function store(ResourceRequest $request)
    {
        $resource = $this->resourceService->store($request);
        return new ResourceResource($resource, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Resource  $resource
     * @return ResourceResource
     */
    public function show(Resource $resource)
    {
        $resource->load(['media', 'contacts']);
        return new ResourceResource($resource, true);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ResourceRequest  $request
     * @param  \App\Models\Resource  $resource
     * @return ResourceResource
     */
    public function update(ResourceRequest $request, Resource $resource)
    {
        $this->authorize('update', $resource);

        $resource = $this->resourceService->update($resource, $request);
        return new ResourceResource($resource, true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Resource  $resource
     * @return \Illuminate\Http\Response
     */
    public function destroy(Resource $resource)
    {
        $this->authorize('delete', $resource);

        $this->resourceService->delete($resource);
        return response()->json(['success' => true]);
    }

    public function action(Resource $resource, string $action)
    {
        $result = match ($action) {
            'favorite' => $this->resourceService->favorite($resource),
            default => ['error' => 'Invalid action'] // TODO: fix
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
