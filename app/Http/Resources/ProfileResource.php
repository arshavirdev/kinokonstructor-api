<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $showDetails = $user->id === $this->user_id || in_array($user->role, ['admin', 'moderator']);
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
            'gender' => $this->gender,

            'phone' => $this->when($showDetails, $this->phone),
            'email' => $this->when($showDetails, $this->user->email),
            'socials_vk' => $this->socials_vk,
            'socials_tg' => $this->socials_tg,
            'socials_ok' => $this->socials_ok,

            'org' => $this->org,
            'entrepreneur' => $this->entrepreneur,

            'city' => $this->city,
            'birthday' => $this->when($showDetails, $this->birthday->format('Y-m-d')),
            'age' => $this->birthday->age,

            'occupation_ids' => $this->occupations->pluck('id'),

            'experience' => $this->experience,
            'education' => $this->education,
            'projects' => $this->customProjects,

            'regions' => $this->regions,

            'portfolio' => $this->portfolio,
            'mass_media_mentions' => $this->mass_media_mentions,

            'moderation' => $this->when($showDetails, count($this->moderationStatus) > 0 ? new ModerationResource($this->moderationStatus[0]) : null),
        ];
    }
}
