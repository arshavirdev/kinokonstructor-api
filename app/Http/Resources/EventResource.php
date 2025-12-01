<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function __construct($resource, private bool $withDetails = false)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = auth()->user();
        $is_owner = (string) $this->owner_id === (string) $user?->profile?->id;
        $privacy = is_array($this->privacy_hide)
            ? $this->privacy_hide
            : explode(',', (string) $this->privacy_hide);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'category' => $this->category,
            'location' => $this->location,
            'format' => $this->format,
            'parameters' => $this->parameters,
            'company' => $this->company,
            'is_owner' => $is_owner,
            'is_favorite' => (bool) $this->is_favorite,
            'is_archived' => $this->is_archived,
            'is_recorded' => $this->is_recorded,
            'created_at' => $this->created_at,
            'date' => $this->date,
            'region_ids' => $this->region_ids,
            'contacts' => new ContactsResource($this->contacts[0] ?? []),
            'event_contacts' => isset($this->contact) ?
                [
                    'phone' => $this->contact->phone ?? [],
                    'email' => $this->contact->email ?? [],
                    'website' => $this->contact->website ?? [],
                    'socials' => $this->contact->socials ?? [],
                    'other' => $this->contact->other ?? [],
                    'telVisible' => !isset($this->privacy_hide) || !in_array('phone', $privacy),
                    'emailVisible' => !isset($this->privacy_hide) || !in_array('email', $privacy),
                ]
                : [],
            'external_link' => $this->external_link,
            Event::IMAGES_FILES => MediaResource::collection($this->getMedia(Event::IMAGES_FILES)),
            Event::FILES => MediaResource::collection($this->getMedia(Event::FILES))
        ];
    }
}
