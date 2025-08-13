<?php

namespace App\Http\Resources;

use App\Models\Course;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = auth()->user();
        $is_owner = (string) $this->owner_id === (string) $user?->profile?->id;
        $organization = $this->owner->org
            ? array_merge($this->owner->org, ['email' => $this->owner->user->email, 'id' => $this->owner->id])
            : [];

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'study_format' => $this->study_format,
            'region_ids' => $this->region_ids,
            'duration' => $this->duration,
            'price' => $this->price,
            'start_date' => $this->start_date,
            'application_start_date' => $this->application_start_date,
            'application_end_date' => $this->application_end_date,
            'teachers' => TeacherResource::collection($this->teachers()->get()),
            'semesters' => SemesterResource::collection($this->semesters()->get()),
            'lessons' => LessonResource::collection($this->lessons()->withoutSemester()->get()),
            'is_owner' => $is_owner,
            'organization' => $organization,
            'created_at' => $this->created_at,
            'is_favorite' => (bool) $this->is_favorite,
            'is_archived' => $this->is_archived,
            Course::FILES_MEDIA => MediaResource::collection($this->getMedia(Course::FILES_MEDIA)),
        ];
    }
}
