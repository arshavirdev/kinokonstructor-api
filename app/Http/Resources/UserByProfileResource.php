<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class UserByProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = optional($this->user);
        $isAnonymousMember = $this->member_id !== null && !$this->user;
        $avatar = $this->getFirstMedia('avatar');
        return [
            'id' => $isAnonymousMember ? $this->member_id : $user->id,
            'avatar' => new AvatarResource($avatar),
            'name' => $this->firstname ?? $user->name,
            'username' => $isAnonymousMember ? $this->member_id : $user->username,
            'email' => $user->email,
            'emailVerified' => $isAnonymousMember ? true : (bool)$user->email_verified_at,
            'roles' => $isAnonymousMember ? ['specialist'] : $user->roles,
            'profile' => new ProfileBriefResource($this),
            'email_verified_at' => $isAnonymousMember ? $this->created_at : $user->email_verified_at,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at ?? $this->updated_at,
        ];
    }
}
