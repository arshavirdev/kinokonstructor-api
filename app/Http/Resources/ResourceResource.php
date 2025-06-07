<?php

namespace App\Http\Resources;

use App\Models\Resource;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    public function __construct($resource, private bool $withDetails = false)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = auth()->user();
        $is_owner = (string) $this->owner_id === (string) $user?->profile?->id;

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'company' => $this->company,
            'parameters' => $this->parameters,
            'category' => $this->category,
            'region_id' => $this->region_id,
            'created_at' => $this->created_at,
            'is_owner' => $is_owner,
            'is_favorite' => (bool) $this->is_favorite,
            'is_archived' => $this->is_archived,
            Resource::IMAGES_FILES => MediaResource::collection($this->getMedia(Resource::IMAGES_FILES)),
            Resource::FILES => MediaResource::collection($this->getMedia(Resource::FILES))
        ];

        if ($this->withDetails) {
            $data['contacts'] = new ContactsResource($this->contacts[0] ?? []);
        }

        return $data;
    }
}
