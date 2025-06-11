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
            'employment_type' => $this->employment_type,
            'position_id' => $this->position_id,
            'department_id' => $this->department_id,
            'salary' => (float) $this->salary,
            'experience' => $this->experience,
            'is_experience_required' => $this->is_experience_required,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'region_id' => $this->region_id,
            'company' => (object) ($this->company ?? []),
            'is_owner' => $is_owner,
            'is_favorite' => (bool) $this->is_favorite,
            'is_archived' => $this->is_archived,
            'contacts' => new ContactsResource($this->contacts[0] ?? [])
        ];

        return $data;
    }
}
