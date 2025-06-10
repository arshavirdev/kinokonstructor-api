<?php

namespace App\Service;

use App\DTOs\MediaSyncDataDTO;
use App\Models\Resource;
use App\Http\Requests\ResourceRequest;
use App\Models\User;
use App\Service\Media\MediaService;
use App\Service\Shared\ContactHandlerService;
use Illuminate\Support\Facades\Auth;

class ResourceService
{
    function __construct(private MediaService $mediaService, private ContactHandlerService $contactHandlerService)
    {
    }

    public function store(ResourceRequest $request): Resource
    {
        $authUser = auth()->user();
        $data = $request->except([Resource::IMAGES_FILES]);
        $data['owner_id'] = $authUser->profile->id;

        $resource = Resource::create($data);

        if ($request->has('contacts')) {
            $this->contactHandlerService->handle($resource, $request->get('contacts'), $authUser);
        }

        $imagesMediaDto = new MediaSyncDataDTO(
            $request->file(Resource::IMAGES_FILES, []),
            $request->post(Resource::IMAGES_FILES, [])
        );

        $filesMediaDto = new MediaSyncDataDTO(
            $request->file(Resource::FILES, []),
            $request->post(Resource::FILES, [])
        );

        $this->mediaService->syncMediaCollection(
            $resource,
            $imagesMediaDto,
            Resource::IMAGES_FILES
        );

        $this->mediaService->syncMediaCollection(
            $resource,
            $filesMediaDto,
            Resource::FILES
        );

        $resource->load(['media', 'contacts']);

        return $resource;
    }

    public function update(Resource $resource, ResourceRequest $request): resource
    {
        $authUser = auth()->user();
        $data = $request->except([Resource::IMAGES_FILES]);
        $resource->update($data);

        if ($request->has('contacts')) {
            $this->contactHandlerService->handle($resource, $request->get('contacts'), $authUser);
        }

        $imagesMediaDto = new MediaSyncDataDTO(
            $request->file(Resource::IMAGES_FILES, []),
            $request->post(Resource::IMAGES_FILES, [])
        );

        $filesMediaDto = new MediaSyncDataDTO(
            $request->file(Resource::FILES, []),
            $request->post(Resource::FILES, [])
        );

        $this->mediaService->syncMediaCollection(
            $resource,
            $imagesMediaDto,
            Resource::IMAGES_FILES
        );

        $this->mediaService->syncMediaCollection(
            $resource,
            $filesMediaDto,
            Resource::FILES
        );

        $resource->load(['media', 'contacts']);

        return $resource;
    }

    public function delete(Resource $resource): bool
    {
        $resource->clearMediaCollection(Resource::IMAGES_FILES);
        return $resource->delete();
    }

    /**
     * Favorite/Unfavorite
     * @param \App\Models\Resource $resource
     * @return array{is_favorite: bool}
     */
    public function favorite(Resource $resource)
    {
        $userId = Auth::id();
        $exists = $resource->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $resource->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $resource->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    /**
     * Archive
     * @param \App\Models\Resource $resource
     * @return array{is_archived: bool}
     */
    public function archive(Resource $resource)
    {
        if ($resource->update(['is_archived' => true])) {
            return ['is_archived' => true];
        }

        return ['is_archived' => false];
    }

    /**
     * Unarchive
     * @param \App\Models\Resource $resource
     * @return array{is_archived: bool}
     */
    public function unarchive(Resource $resource)
    {
        if ($resource->update(['is_archived' => false])) {
            return ['is_archived' => false];
        }

        return ['is_archived' => true];
    }
}
