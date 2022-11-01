<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProfileResource extends JsonResource
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
            'user_id' => $this->user_id,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'avatar' => new AvatarResource($this->getFirstMedia(Profile::AVATAR_MEDIA)),
            'attachments' => MediaResource::collection($this->getMedia(Profile::ATTACHMENT_MEDIA)),

            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'middlename' => $this->middlename,

            'phone' => $this->phone,
            'email' => $this->user->email,
            'socials_vk' => $this->socials_vk,
            'socials_tg' => $this->socials_tg,
            'socials_ok' => $this->socials_ok,

            'org' => $this->org,
            'entrepreneur' => $this->entrepreneur,

            'city' => $this->city,
            'birthday' => $this->birthday->format('Y-m-d'),
            'age' => $this->birthday->age,
            'occupation' => $this->occupation,
            'occupation_id' => $this->occupation->id,

            'experience' => $this->experience,
            'education' => $this->education,
            'projects' => $this->customProjects,

            'regions' => $this->regions,

            'portfolio' => $this->portfolio,
            'mass_media_mentions' => $this->mass_media_mentions,
        ];
    }
}
