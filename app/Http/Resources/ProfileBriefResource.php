<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileBriefResource extends JsonResource
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
            'avatar' => new AvatarResource($this->getFirstMedia(Profile::AVATAR_MEDIA)),
            'is_verified' => $this->is_verified,
            'status' => $this->status,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'middlename' => $this->middlename,
            'occupation_ids' => $this->occupations->pluck('id'),
            'age' => $this->birthday?->age,
            'city' => $this->city,
        ];
    }
}
