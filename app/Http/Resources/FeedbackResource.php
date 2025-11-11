<?php

namespace App\Http\Resources;

use App\Models\Contest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
            'owner' => new ProfileBriefResource($this->owner),
            'feedbackable' => $this->whenLoaded('feedbackable'),
            'created_at' => $this->created_at
        ];
    }
}