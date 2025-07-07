<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContestApplicationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'status' => $this->status,
            'contest_id' => $this->contest_id,
            'contest_title' => $this->contest?->title,
            'applicant_id' => $this->applicant_id,
            'applicant_full_name' => $this->applicant->fullname,
            'project_id' => $this->project_id,
            'project_title' => $this->project?->title,
            'created_at' => $this->created_at,
        ];
    }
}
