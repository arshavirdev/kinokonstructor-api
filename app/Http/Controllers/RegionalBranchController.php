<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionalBranch\RegionalBranchRequest;
use App\Http\Resources\RegionalBranch\RegionalBranchResource;
use App\Models\RegionalBranch;
use App\Service\RegionalBranchService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RegionalBranchController extends Controller
{
    public function __construct(private RegionalBranchService $regionalBranchService)
    {

    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RegionalBranch::query()->with('media');

        if ($request->has('region_id')) {
            $query = $query->where('region_id', $request->input('region_id'));
        }

        $regionalBranches = $query->orderBy('created_at', 'desc')->paginate($request->input('pageSize', 10));

        return RegionalBranchResource::collection($regionalBranches);
    }

    public function show(RegionalBranch $regionalBranch): RegionalBranchResource
    {
        $regionalBranch->load(['media', 'news', 'members', 'contacts']);
        return new RegionalBranchResource($regionalBranch, true);
    }

    public function store(RegionalBranchRequest $request): RegionalBranchResource
    {
        $regionalBranch = $this->regionalBranchService->store($request);
        return new RegionalBranchResource($regionalBranch);
    }

    public function update(RegionalBranch $regionalBranch, RegionalBranchRequest $request): RegionalBranchResource
    {
        $regionalBranch = $this->regionalBranchService->update($regionalBranch, $request);
        return new RegionalBranchResource($regionalBranch);
    }

    public function destroy(RegionalBranch $regionalBranch): \Illuminate\Http\JsonResponse
    {
        $regionalBranch->delete();
        return response()->json(['message' => 'Branch and related data deleted.']);
    }

}
