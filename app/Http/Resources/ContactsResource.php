<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContactsResource extends JsonResource
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
            'phone' => $this->phone ?? [],
            'email' => $this->email ?? [],
            'website' => $this->website ?? [],
            'socials' => $this->socials ?? [],
            'other' => $this->other ?? [],
        ];
    }
}
