<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (count($this->generated_conversions) === 0) {
            $url = $this->getFullUrl();
        } else {
            $url = [];
            foreach ($this->generated_conversions as $name => $isset) {
                $url[$name] = $this->getFullUrl($name);
            }
        }
        $name = $this->name . '.' . pathinfo($this->file_name, PATHINFO_EXTENSION);
        return [
            'id' => $this->id,
            'name' => $name,
            'mime' => $this->mime_type,
            'size' => $this->size,
            'url' => $url,
        ];
    }
}
