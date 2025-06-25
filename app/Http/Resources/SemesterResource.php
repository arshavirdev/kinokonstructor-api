<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
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
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->start_date,
            'created_at' => $this->created_at,
            'lessons' => LessonResource::collection($this->lessons()->get()),
        ];
    }
}
