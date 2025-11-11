<?php

namespace App\Http\Resources;

// use App\Models\EventApplication;
use Illuminate\Http\Resources\Json\JsonResource;

class EventApplicationResource extends JsonResource
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
            'details' => $this->details,
            'event_id' => $this->event_id,
            'event_title' => $this->event?->title,
            'applicant_id' => $this->applicant_id,
            'applicant_full_name' => $this->applicant->fullname,
            'created_at' => $this->created_at,
        ];
    }
}
