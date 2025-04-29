<?php

namespace App\Http\Resources;

use App\Models\Project;
use App\Models\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectBriefResource extends JsonResource
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
            'title' => $this->title,
            'status' => $this->status,
            'format' => $this->format,
            'genre_type' => $this->genre_type,
            'chronography' => $this->chronography,
            'series_count' => $this->series_count,
            'genres' => $this->genres,
            'logline' => $this->logline,
            'locations' => ProjectLocationResource::collection($this->locations),
            'created_at' => $this->created_at,
            'is_favorite' => (bool) $this->is_favorite,
            'is_archived' => $this->is_archived,
            'project_images' => MediaResource::collection($this->getMedia(Project::IMAGES)),
            'total_requests_count' => $this->requests->count(),
            'requests' => [
                Request::LOCATION_REQUEST => RequestResource::collection($this->requests->where('type', Request::LOCATION_REQUEST)),
                Request::SPECIFICATION_REQUEST => RequestResource::collection($this->requests->where('type', Request::SPECIFICATION_REQUEST)),
                Request::EQUIPMENT_REQUEST => RequestResource::collection($this->requests->where('type', Request::EQUIPMENT_REQUEST)),
                Request::OTHER_REQUEST => RequestResource::collection($this->requests->where('type', Request::OTHER_REQUEST)),
                Request::SERVICES_REQUEST => RequestResource::collection($this->requests->where('type', Request::SERVICES_REQUEST)),
            ],
        ];
    }
}
