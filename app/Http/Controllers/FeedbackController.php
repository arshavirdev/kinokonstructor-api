<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{

    public function index(Request $request)
    {
        $query = Feedback::query()
            ->with('owner')
            ->with('feedbackable')
            ->orderBy('id', 'desc');

        if ($type = $request->query('type')) {
           $modelClass = 'App\\Models\\' . ucfirst($type);

           if (!class_exists($modelClass)) {
                return response()->json([
                    'error' => "Invalid feedbackable type: {$type}"
                ], 400);
           }

           $query->where('feedbackable_type', $modelClass);
        }

        $feedbacks = $query->paginate();

        return FeedbackResource::collection($feedbacks);
    }

    public function store(StoreFeedbackRequest $request)
    {
        $user = auth()->user();
        $profileId = $user?->profile?->id;

        $modelClass = 'App\\Models\\' . ucfirst($request->entity_type);
        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Invalid entity type'], 400);
        }

        $entity = $modelClass::find($request->entity_id);
        if (!$entity) {
            return response()->json(['error' => 'Entity not found'], 404);
        }

        $feedback = $entity->feedbacks()->create([
            'owner_id' => $profileId,
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ]);

        return new FeedbackResource($feedback);
    }
}
