<?php

namespace App\Http\Controllers;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Http\Resources\LocationResource;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query()->with(['owner', 'owner.media', 'owner.occupation', 'media']);

        if ($request->has('name'))
            $query = $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        if ($request->has('region'))
            $query = $query->where('region_id', $request->input('region'));
        if ($request->has('city'))
            $query = $query->where('city', $request->input('city'));
        if ($request->has('tags'))
            $query = $query->whereHasTags($request->input('tags'));

        return LocationResource::collection($query->paginate());
    }

    public function store(StoreLocationRequest $request)
    {
        $location = $request->all();
        $profile = Auth::user()->profile;
        $location['owner_id'] = $profile['id'];
//        dd($location);
        return Location::create($location);
//        dd($location, $profile);
//        $profile->locations()->create($request->all());
    }

    public function show(Location $location)
    {
        $location->load(['owner', 'media']);
        return new LocationResource($location);
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        //
    }

    public function destroy(Location $location)
    {
        $location->delete();
    }
}
