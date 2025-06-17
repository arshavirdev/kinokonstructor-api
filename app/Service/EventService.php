<?php

namespace App\Service;

use App\DTOs\MediaSyncDataDTO;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Http\Requests\StoreEventRequest;
use App\Service\Media\MediaService;
use App\Service\Shared\ContactHandlerService;
use Illuminate\Support\Facades\Auth;

class EventService
{
    function __construct(
        private ContactHandlerService $contactHandlerService,
        private MediaService $mediaService
    ) {
    }

    public function store(StoreEventRequest $request): Event
    {
        $authUser = auth()->user();
        $data = $request->validated();
        $data['owner_id'] = $authUser->profile->id;

        $event = Event::create($data);

        if ($request->has('contacts') && isset($data['contacts'])) {
            $this->contactHandlerService->handle($event, $data['contacts'], $authUser);
        }

        $imagesMediaDto = new MediaSyncDataDTO(
            $request->file(Event::IMAGES_FILES, []),
            $request->post(Event::IMAGES_FILES, [])
        );

        $filesMediaDto = new MediaSyncDataDTO(
            $request->file(Event::FILES, []),
            $request->post(Event::FILES, [])
        );

        $this->mediaService->syncMediaCollection(
            $event,
            $imagesMediaDto,
            Event::IMAGES_FILES
        );

        $this->mediaService->syncMediaCollection(
            $event,
            $filesMediaDto,
            Event::FILES
        );

        $event->load(['media', 'contacts']);
        return $event;
    }

    public function update(Event $event, UpdateEventRequest $request): Event
    {
        $data = $request->validated();
        $authUser = auth()->user();
        $event->update($data);

        if ($request->has('contacts') && isset($data['contacts'])) {
            $this->contactHandlerService->handle($event, $data['contacts'], $authUser);
        }

        $imagesMediaDto = new MediaSyncDataDTO(
            $request->file(Event::IMAGES_FILES, []),
            $request->post(Event::IMAGES_FILES, [])
        );

        $filesMediaDto = new MediaSyncDataDTO(
            $request->file(Event::FILES, []),
            $request->post(Event::FILES, [])
        );

        $this->mediaService->syncMediaCollection(
            $event,
            $imagesMediaDto,
            Event::IMAGES_FILES
        );

        $this->mediaService->syncMediaCollection(
            $event,
            $filesMediaDto,
            Event::FILES
        );

        $event->load(['media', 'contacts']);
        return $event;
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }

    /**
     * Favorite/Unfavorite
     * @param \App\Models\Event $event
     * @return array{is_favorite: bool}
     */
    public function favorite(Event $event)
    {
        $userId = Auth::id();
        $exists = $event->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $event->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $event->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    /**
     * Archive
     * @param \App\Models\Event $event
     * @return array{is_archived: bool}
     */
    public function archive(Event $event)
    {
        if ($event->update(['is_archived' => true])) {
            return ['is_archived' => true];
        }

        return ['is_archived' => false];
    }

    /**
     * Unarchive
     * @param \App\Models\Event $event
     * @return array{is_archived: bool}
     */
    public function unarchive(Event $event)
    {
        if ($event->update(['is_archived' => false])) {
            return ['is_archived' => false];
        }

        return ['is_archived' => true];
    }
}
