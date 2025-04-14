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
        $isSameUser = $user->id === $this->user_id;
        $isPrivileged = in_array($user->role, ['admin', 'moderator']);
        $isNotGuest = $user->role !== 'guest';
        $showDetails = $isSameUser || $isPrivileged;
        $showPhone = $isSameUser || $isPrivileged || ($isNotGuest && !in_array('phone', $this->privacy_hide));
        $showEmail = $isSameUser || $isPrivileged || ($isNotGuest && !in_array('email', $this->privacy_hide));
        $showSocials = $isSameUser || $isPrivileged || ($isNotGuest && !in_array('socials', $this->privacy_hide));
        $showMemberId = $isPrivileged;
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'member_id' => $this->when($showMemberId, $this->member_id),
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'avatar' => new AvatarResource($this->getFirstMedia(Profile::AVATAR_MEDIA)),
            'attachments' => MediaResource::collection($this->getMedia(Profile::ATTACHMENT_MEDIA)),

            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'middlename' => $this->middlename,
            'gender' => $this->gender,

            'phone' => $this->when($showPhone, $this->phone),
            'email' => $this->when($showEmail, $this->user?->email),
            'additional_information' => $this->additional_information,
            'socials_vk' => $this->when($showSocials, $this->socials_vk),
            'socials_tg' => $this->when($showSocials, $this->socials_tg),
            'socials_ok' => $this->when($showSocials, $this->socials_ok),

            'org' => $this->org,
            'entrepreneur' => $this->entrepreneur,

            'city' => $this->city,
            'birthday' => $this->when($showDetails, $this->birthday?->format('Y-m-d')),
            'age' => $this->birthday?->age,

            'occupation_ids' => $this->occupations->pluck('id'),

            'contacts' => $this->whenLoaded('contact', function () {
                return [
                    'phone' => $this->contact->phone ?? [],
                    'email' => $this->contact->email ?? [],
                    'website' => $this->contact->website ?? [],
                    'socials' => $this->contact->socials ?? [],
                    'other' => $this->contact->other ?? [],
                ];
            }),

            'experience' => $this->experience,
            'education' => $this->education,
            'projects' => $this->customProjects,

            'regions' => $this->regions,

            'portfolio' => $this->portfolio,
            'mass_media_mentions' => $this->mass_media_mentions,

            'privacy_hide' => $this->when($user->id === $this->user_id, $this->privacy_hide),

            'moderation' => $this->when($showDetails, count($this->moderationStatus) > 0 ? new ModerationResource($this->moderationStatus[0]) : null),
        ];
    }
}
