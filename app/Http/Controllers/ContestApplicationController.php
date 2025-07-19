<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContestApplicationRequest;
use App\Http\Resources\ContestApplicationsResource;
use App\Models\Contest;
use App\Models\ContestApplication;
use App\Service\ContestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContestApplicationController extends Controller
{
    public function __construct(private ContestService $contestService)
    {

    }

    public function apply(Contest $contest, StoreContestApplicationRequest $request) {
        $result = $this->contestService->apply($contest, $request);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error'],
            ], 409);
        }

        return response()->json($result);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $profileId = $user->profile?->id;
        $contestApplications = ContestApplication::query()
            ->with(['applicant', 'contest', 'project'])
            ->whereHas('contest', function ($query) use ($profileId) {
                $query->where('owner_id', $profileId);
            })
            ->get();

        return ContestApplicationsResource::collection($contestApplications);
    }

    public function show(ContestApplication $contestApplication)
    {
        $contestApplication = $contestApplication->load(['applicant', 'contest', 'project']);
        return new ContestApplicationsResource($contestApplication);
    }
}
