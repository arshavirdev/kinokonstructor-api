<?php

namespace App\Http\Resources;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'avatar' =>  new AvatarResource($this->owner->getFirstMedia(Profile::AVATAR_MEDIA)),
            'position_id' => $this->position_id,
            'work_format' => $this->work_format,
            'salary_expectation' => (float) $this->salary_expectation,
            'location' => $this->location,
            'experience_years' => $this->experience_years,
            'experience_description' => $this->experience_description,
            'bio' => $this->bio,
            'contacts' => new ContactsResource($this->contacts[0] ?? [])
        ];
    }
}
