<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $profile = $this->profile;
        $avatar = is_null($profile) ? null : $profile->getFirstMedia('avatar');
        return [
            'id' => $this->id,
            'avatar' => new AvatarResource($avatar),
            'name' => optional($profile)->firstname ?? $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'emailVerified' => (bool)$this->email_verified_at,
            'role' => $this->role,
            'profile' => new ProfileResource($this->profile)
        ];
    }
}
