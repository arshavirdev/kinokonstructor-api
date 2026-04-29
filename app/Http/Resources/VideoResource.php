<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Models\Video;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class VideoResource extends JsonResource
{
    protected bool $withDetails;

    public function __construct($resource, $withDetails = false)
    {
        parent::__construct($resource);
        $this->withDetails = $withDetails;
    }

    /**
     * Transform the resource into an array. 2
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $userId = Auth::guard('sanctum')->id();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'video_link' => $this->video_link,
            'external_link' => $this->external_link,
            'comments' => $this->when(
                $this->withDetails,
                CommentResource::collection($this->comments)
            ),
            'is_favorite' => (bool) $this->is_favorite,
            'video_file' => new MediaResource($this->getFirstMedia(Video::VIDEO_FILE)),
            'image_file' => new MediaResource($this->getFirstMedia(Video::IMAGE_FILE)),
            'is_owner' => $userId !== null && (int)$this->owner_id === (int)$userId,
            'owner' => isset($this->owner->profile) ? [
                'roles' => $this->owner->roles,
                'fullname' => $this->owner->profile->fullname,
                'avatar' => new AvatarResource($this->owner->profile->getFirstMedia(Profile::AVATAR_MEDIA)),
            ] : [],
            'created_at' => $this->created_at,
        ];
    }
}
