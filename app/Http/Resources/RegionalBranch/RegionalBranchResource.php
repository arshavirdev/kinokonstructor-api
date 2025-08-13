<?php

namespace App\Http\Resources\RegionalBranch;

use App\Http\Resources\AvatarResource;
use App\Http\Resources\ContactsResource;
use App\Http\Resources\MediaResource;
use App\Models\BranchMember;
use App\Models\Contact;
use App\Models\Profile;
use App\Models\RegionalBranch;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionalBranchResource extends JsonResource
{

    private bool $withDetails;

    public function __construct($resource, $withDetails = false)
    {
        parent::__construct($resource);
        $this->withDetails = $withDetails;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $authUserProfileId = auth()->user()?->profile?->id;

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'contacts' => new ContactsResource($this->contacts->first() ?? new Contact()),
            'address' => $this->address,
            'city' => $this->city,
        ];


        if (!$this->withDetails) {
            return $data;
        }

        return array_merge($data, [
                'year' => $this->year,
                'region_id' => $this->region_id,
                'description' => $this->description,
                'manager' => $this->manager,
                'members' => BranchMemberResource::collection($this->members),
                'news' => BranchNewsResource::collection($this->news),
                'docs_files' => MediaResource::collection($this->getMedia(RegionalBranch::DOCS_FILES)),
                'is_owner' => $this->owner_id === $authUserProfileId,
                'owner' => isset($this->owner) ? [
                    'fullname' => $this->owner->fullname,
                    'avatar' => new AvatarResource($this->owner->getFirstMedia(Profile::AVATAR_MEDIA)),
                ] : []
            ]
        );

    }
}
