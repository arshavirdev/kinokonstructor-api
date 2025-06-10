<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VacancyResource extends JsonResource
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

        $data = [
            'id' => $this->id,
            'format' => $this->format,
            'position_id' => $this->position_id,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'is_owner' => $is_owner,
            'is_favorite' => (bool) $this->is_favorite,
            'is_archived' => $this->is_archived,
            'contacts' => new ContactsResource($this->contacts[0] ?? [])
        ];

        return $data;
    }
}
