<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'synopsis' => $this->synopsis,
            'relevance' => $this->relevance,
            'additional' => $this->resource->additional,
            'budget' => $this->budget,
            'co_financing' => $this->co_financing,
            'members' => [],
            'custom_members' => $this->custom_members,

            'extended_synopsis' => new MediaResource($this->getFirstMedia(Project::EXTENDED_SYNOPSIS_MEDIA)),
            'attachments' => MediaResource::collection($this->getMedia(Project::ATTACHMENTS_MEDIA)),
            'cast_reference' => new MediaResource($this->getFirstMedia(Project::CAST_MEDIA)),
            'costumes' => new MediaResource($this->getFirstMedia(Project::COSTUMES_MEDIA)),
            'makeup' => new MediaResource($this->getFirstMedia(Project::MAKEUP_MEDIA)),
            'decorations' => new MediaResource($this->getFirstMedia(Project::DECORATIONS_MEDIA)),
            'location_reference' => new MediaResource($this->getFirstMedia(Project::LOCATIONS_MEDIA)),
            'financial_plan' => new MediaResource($this->getFirstMedia(Project::FINANCIAL_PLAN_MEDIA)),
            'financial_proof' => new MediaResource($this->getFirstMedia(Project::FINANCIAL_PROOF_MEDIA)),
            'partnership_proof' => MediaResource::collection($this->getMedia(Project::PARTNERSHIP_PROOF_MEDIA)),

            'moderation' => $this->when($this->status === 'moderation', [
                'startedAt' => $this->updatedAt,
                'status' => 'pending',
                'email' => 'avzaytsev@kinofond.ru'
            ], null)
        ];
    }
}
