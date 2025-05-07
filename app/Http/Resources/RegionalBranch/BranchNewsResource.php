<?php

namespace App\Http\Resources\RegionalBranch;

use App\Http\Resources\MediaResource;
use App\Models\BranchNews;
use App\Models\RegionalBranch;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchNewsResource extends JsonResource
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
            'category' => $this->category,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'created_at' => $this->created_at,
            'image_file' => new MediaResource($this->getFirstMedia(BranchNews::IMAGE)),
        ];
    }
}
