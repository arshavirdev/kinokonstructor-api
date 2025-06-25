<?php

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'video_link' => $this->video_link,
            'address' => $this->address,

            'speaker_first_name' => $this->speaker_first_name,
            'speaker_last_name' => $this->speaker_last_name,
            'speaker_bio' => $this->speaker_bio,

            'course_id' => $this->course_id,
            'semester_id' => $this->semester_id,

            'created_at' => $this->created_at,
        ];
    }
}
