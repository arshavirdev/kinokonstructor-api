<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
//        $photos = $request->getMedia();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'photos' => [],
            'tags' => $this->tags,
            'region_id' => $this->region_id,
            'city' => $this->city,
            'latlng' => $this->latlng,
            'owner' => new ProfileBriefResource($this->owner),
        ];
    }
}
