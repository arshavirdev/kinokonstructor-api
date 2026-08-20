<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'user' => isset($this->user) ? [
                'fullname' => $this->user->profile->fullname,
                'avatar' => new AvatarResource($this->user->profile->getFirstMedia(Profile::AVATAR_MEDIA)),
            ] : [],
            'body' => $this->body,
            'is_hidden' => $this->is_hidden,
            'created_at' => $this->created_at,
        ];
    }
}
