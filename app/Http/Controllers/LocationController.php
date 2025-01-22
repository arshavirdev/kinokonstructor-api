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
        $query = Location::query()->with(['owner', 'owner.media', 'owner.occupations', 'media']);

        if ($request->has('type') && $request->input('type') === 'my') {
            $profileId = Auth::user()->profile?->id;
            if (!$profileId) abort(421);
            $query = $query->where('owner_id', $profileId);
        }
        if ($request->has('name') && $request->input('name'))
            $query = $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        if ($request->has('region'))
            $query = $query->where('region_id', $request->input('region'));
        if ($request->has('city') && $request->input('city'))
            $query = $query->where('city', $request->input('city'));
        if ($request->has('tags'))
            $query = $query->whereHasTags($request->input('tags'));

        return LocationResource::collection($query->paginate());
    }

    private function syncMedia(Location $location, $params)
    {
        if (array_key_exists('photos', $params)) {
            $requestPhotos = collect($params['photos']);
            $toSavePhotos = $requestPhotos->filter(fn($item) => is_object($item));
            $toKeepPhotosIds = $requestPhotos->filter(fn($item) => !is_object($item))->map(fn($id) => ['id' => (int)$id]);
            $location->clearMediaCollectionExcept(Location::GALLERY_MEDIA, $toKeepPhotosIds);
            foreach ($toSavePhotos as $photo) {
                $location->addMedia($photo)->toMediaCollection(Location::GALLERY_MEDIA);
            }
        }
    }

    public function store(StoreLocationRequest $request)
    {
        $params = $request->all();
        $profile = Auth::user()->profile;
        $params['owner_id'] = $profile['id'];
        $location = Location::create($params);
        $this->syncMedia($location, $params);
        return $location;
    }

    public function show(Location $location)
    {
        $location->load(['owner', 'media']);
        return new LocationResource($location);
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        $params = $request->all();

        $this->syncMedia($location, $params);
        $location->fill($params);
        $location->save();

        return [];
    }

    public function destroy(Location $location)
    {
        $location->delete();
    }
}
