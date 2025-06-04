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
        $query = Resource::query()->with(['owner']);
        $resources = $query->orderBy('created_at', 'desc')->paginate($request->input('pageSize', 10));
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
        $this->resourceService->delete($resource);
        return response()->json(['success' => true]);
    }
}
