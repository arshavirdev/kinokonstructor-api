<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventApplication;
use App\Service\EventService;
use App\Http\Requests\StoreEventApplicationRequest;
use App\Http\Resources\EventApplicationResource;
use Illuminate\Support\Facades\Auth;

class EventApplicationController extends Controller
{
    public function __construct(private EventService $eventService)
    {

    }

    public function index()
    {
        $user = Auth::user();
        $profileId = $user->profile?->id;
        $eventApplications = EventApplication::query()
            ->with(['applicant', 'event'])
            ->whereHas('event', function ($query) use ($profileId) {
                $query->where('owner_id', $profileId);
            })
            ->get();

        return EventApplicationResource::collection($eventApplications);
    }

    public function show(EventApplication $eventApplication)
    {
        $eventApplication = $eventApplication->load(['applicant', 'event']);
        return new EventApplicationResource($eventApplication);
    }

    public function apply(Event $event, StoreEventApplicationRequest $request) {
        $result = $this->eventService->apply($event, $request);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error'],
            ], 409);
        }

        return response()->json($result);
    }
}
