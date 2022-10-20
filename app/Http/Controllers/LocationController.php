<?php

namespace App\Http\Controllers;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Http\Resources\LocationResource;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query()->with('owner');

        if ($request->has('name'))
            $query = $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        if ($request->has('region'))
            $query = $query->where('region', $request->input('region'));
        if ($request->has('city'))
            $query = $query->where('city', $request->input('city'));
        if ($request->has('tags'))
            $query = $query->whereHasTags($request->input('tags'));

        return LocationResource::collection($query->paginate());
    }

    public function store(StoreLocationRequest $request)
    {
        //
    }

    public function show(Location $location)
    {
        //
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
