<?php

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'tags' => $this->tags,
            'region_id' => $this->region_id,
            'city' => $this->city,
            'latlng' => $this->latlng,
            'photos' => MediaResource::collection($this->getMedia(Location::GALLERY_MEDIA)),
            'owner' => new ProfileBriefResource($this->owner),
        ];
    }
}
