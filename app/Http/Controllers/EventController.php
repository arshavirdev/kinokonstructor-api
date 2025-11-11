<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventBriefResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Service\EventService;
use Symfony\Component\HttpFoundation\Request;

class EventController extends Controller
{
    function __construct(private EventService $eventService)
    {
    }

    /**
     * Display a listing of the event.
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user?->id;
        $profileId = $user?->profile?->id;

        $query = Event::query()->with(['owner', 'media'])
            ->withCount([
                'favorites as is_favorite' => fn($q) => $q->where('user_id', $userId),
            ]);

        $query->when($request->get('type') === 'my', function ($q) use ($profileId) {
            $q->where('owner_id', $profileId);
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->get('search'));
            $q->whereRaw('LOWER(title) LIKE ?', ['%' . $search . '%']);
        });

        if ($request->has('favorite') && $request->input('favorite') === 'true' ) {
            $query->whereHas('favorites', function ($q) use ($profileId) {
                $q->where('user_id', $profileId);
            });
        }

        $query->when($request->filled('format'), function ($q) use ($request) {
            $q->where('format', $request->input('format'));
        });

        $query->when($request->filled('date'), function ($q) use ($request) {
            $date = $request->input('date');
            [$month, $year] = explode('-', $date);

            $q->whereYear('date', $year)
                ->whereMonth('date', $month);
        });

        if ($request->has('filter')) {
            if ($request->has('filter.location')) {
                $query = $query->where('region_ids', 'like', '%'.$request->input('filter.location').'%');
            }
        }

        $resources = $query->orderBy('created_at', 'DESC')
            ->paginate($request->input('pageSize', 10));
        return EventBriefResource::collection($resources);
    }

    /**
     * Store a newly created event in storage.
     *
     * @param  StoreEventRequest  $request
     * @return EventResource
     */
    public function store(StoreEventRequest $request)
    {
        $event = $this->eventService->store($request);
        return new EventResource($event);
    }

    /**
     * Display the specified event.
     *
     * @param  \App\Models\Event  $event
     * @return EventResource
     */
    public function show(Event $event)
    {
        return new EventResource($event);
    }

    /**
     * Update the specified event in storage.
     *
     * @param  UpdateEventRequest  $request
     * @param  \App\Models\Event  $event
     * @return EventResource
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        $this->authorize('update', $event);
        $event = $this->eventService->update($event, $request);
        return new EventResource($event);
    }

    /**
     * Remove the specified event from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $this->eventService->delete($event);
        return response()->json(['success' => true]);
    }

    public function action(Event $event, string $action)
    {
        $result = match ($action) {
            'favorite' => $this->eventService->favorite($event),
            'archive' => $this->eventService->archive($event),
            'unarchive' => $this->eventService->unarchive($event),
            default => ['error' => 'Invalid action']
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
