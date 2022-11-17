<?php

namespace App\Http\Controllers;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Profile;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\ProfileBriefResource;
use App\Http\Resources\ProfileBriefCollection;


class ProfileController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // $this->authorizeResource(Profile::class, 'profile');
    }

    public function index(Request $request)
    {
        // $this->authorize('viewAny');
        $query = Profile::query()->with(['user', 'media', 'occupation'])->orderBy('is_verified', 'desc')->onlyAccepted();

        if ($request->has('type'))
            $query = $request->input('type') === 'actor' ? $query->isActor() : $query->isSpecialist();

        if ($request->has('fullname'))
            $query = $query->whereFullname($request->input('fullname'));

        if ($request->has('status'))
            $query = $query->where('status', $request->input('status'));

        if ($request->has('gender'))
            $query = $query->where('gender', $request->input('gender'));

        if ($request->has('occupation_id'))
            $query = $query->where('occupation_id', $request->input('occupation_id'));

        if ($request->has('city'))
            $query = $query->where('city', 'ilike', $request->input('city'));

        $profiles = $query->paginate();

        return ProfileBriefResource::collection($profiles);
    }

    public function show(Profile $profile)
    {
        $profile->load(['experience', 'education', 'customProjects', 'projects', 'media', 'occupation']);
        return new ProfileResource($profile);
    }
}
