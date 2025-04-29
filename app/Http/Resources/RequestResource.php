<?php

namespace App\Http\Resources;

use App\Models\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'location' => $this->location,
            'season' => $this->season,
            'info' => $this->info,
            'docs_files' => MediaResource::collection($this->getMedia(Request::DOCS_FILES)),
            'images_files' => MediaResource::collection($this->getMedia(Request::IMAGES_FILES)),
        ];
    }
}
