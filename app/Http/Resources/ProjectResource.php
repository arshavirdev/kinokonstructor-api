<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Models\Project;
use App\Traits\Moderation\Status;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        $moderation = \Arr::get($this->moderationStatus, '0');
        $isOwner = (string)$this->owner_id === (string)$user?->profile?->id;
        $isPrivileged = in_array($user->role, ['admin', 'moderator']);
        $canViewBudget = $isOwner || $isPrivileged;
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

            'budget' => $this->when($canViewBudget, $this->budget),
            'co_financing' => $this->when($canViewBudget, $this->co_financing),

            'custom_members' => $this->custom_members,
            'audio_reference' => $this->audio_reference,
            'members' => ProfileMemberResource::collection($this->memberInvites),
            'locations' => ProjectLocationResource::collection($this->locations),

            'extended_synopsis' => new MediaResource($this->getFirstMedia(Project::EXTENDED_SYNOPSIS_MEDIA)),
            'attachments' => MediaResource::collection($this->getMedia(Project::ATTACHMENTS_MEDIA)),
            'cast_reference' => new MediaResource($this->getFirstMedia(Project::CAST_MEDIA)),
            'costumes' => new MediaResource($this->getFirstMedia(Project::COSTUMES_MEDIA)),
            'makeup' => new MediaResource($this->getFirstMedia(Project::MAKEUP_MEDIA)),
            'decorations' => new MediaResource($this->getFirstMedia(Project::DECORATIONS_MEDIA)),
            'location_reference' => new MediaResource($this->getFirstMedia(Project::LOCATIONS_MEDIA)),

            'financial_plan' => $this->when($canViewBudget, new MediaResource($this->getFirstMedia(Project::FINANCIAL_PLAN_MEDIA))),
            'financial_proof' => $this->when($canViewBudget, new MediaResource($this->getFirstMedia(Project::FINANCIAL_PROOF_MEDIA))),

            'partnership_proof' => MediaResource::collection($this->getMedia(Project::PARTNERSHIP_PROOF_MEDIA)),

            'owner' => $this->owner,
            'isOwner' => $isOwner,
            'canEdit' => $isOwner,

            'moderation' => $this->when($this->status === Status::PENDING || $this->status === Status::REJECTED && isset($moderation), [
                'startedAt' => $this->updatedAt,
                'comment' => $moderation?->comment,
                'email' => \Arr::get($moderation, 'data.email'),
                'data' => $moderation?->data
            ], null),
        
            'is_favorited' => (bool) $this->is_favorited,
            'is_archived' => $this->is_archived
        ];
    }
}
