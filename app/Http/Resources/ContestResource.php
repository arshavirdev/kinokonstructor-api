<?php

namespace App\Http\Resources;

use App\Models\Contest;
use Illuminate\Http\Resources\Json\JsonResource;

class ContestResource extends JsonResource
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
            'title' => $this->title,
            'type' => $this->type,
            'years_held' => $this->years_held,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'description' => $this->description,
            'gallery' => MediaResource::collection($this->getMedia(Contest::GALLERY)),
            'conditions' => $this->conditions,
            'deadline_title' => $this->deadline_title,
            'deadline_date' => $this->deadline_date,
            'prizes' => $this->prizes,
            'adjudicator' => $this->adjudicator,
            'organizers' => $this->organizers,
            'documents' => MediaResource::collection($this->getMedia(Contest::DOCUMENTS)),
            'online_application' => $this->online_application,
            'logo' => new MediaResource($this->getFirstMedia(Contest::LOGO)),
            'video' => $this->video,
            'photo_gallery' => MediaResource::collection($this->getMedia(Contest::PHOTO_GALLERY)),
            'partners' => MediaResource::collection($this->getMedia(Contest::PARTNERS)),
            'contacts' => $this->contacts,
        ];
    }
}
