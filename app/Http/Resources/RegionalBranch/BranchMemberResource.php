<?php

namespace App\Http\Resources\RegionalBranch;

use App\Http\Resources\MediaResource;
use App\Models\BranchMember;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchMemberResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'image_file' => new MediaResource($this->getFirstMedia(BranchMember::IMAGE))
        ];
    }
}
