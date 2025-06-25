<?php

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'bio' => $this->bio,
            Teacher::AVATAR_MEDIA => new MediaResource($this->getFirstMedia(Teacher::AVATAR_MEDIA))
        ];
    }
}
