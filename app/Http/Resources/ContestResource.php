<?php

namespace App\Http\Resources;

use App\Models\Contest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ContestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = Auth::user();
        $is_owner = (string) $this->owner_id === (string) $user?->profile?->id;
        $organization = $this->owner->org
            ? array_merge($this->owner->org, ['email' => $this->owner->user->email])
            : [];
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'years_held' => (int) $this->years_held,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'description' => $this->description,
            'gallery' => MediaResource::collection($this->getMedia(Contest::GALLERY)),
            'conditions' => $this->conditions,
            'deadlines' => $this->deadlines,
            'prizes' => $this->prizes,
            'jury' => $this->jury,
            'organizers' => $this->organizers,
            'documents' => MediaResource::collection($this->getMedia(Contest::DOCUMENTS)),
            'online_application' => $this->online_application,
            'logo' => MediaResource::collection($this->getMedia(Contest::LOGO)),
            'video_link' => $this->video_link,
            'photo_gallery' => MediaResource::collection($this->getMedia(Contest::PHOTO_GALLERY)),
            'partners' => MediaResource::collection($this->getMedia(Contest::PARTNERS)),
            'organization' => $organization,
            'is_owner' => $is_owner,
            'is_favorite' => (bool) $this->is_favorite,
            'is_archived' => $this->is_archived,
            'created_at' => $this->created_at,
            'contacts' => isset($this->contacts)
                ? [
                    'website' => $this->contacts->website,
                    'social_media' => $this->contacts->social_media,
                    'email' => $this->contacts->email,
                    'phone' => $this->contacts->phone,
                    'postal_address' => $this->contacts->postal_address,
                    'button_name' => $this->contacts->button_name
                ]
                : []
        ];
    }
}
