<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Service\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    protected VideoService $videoService;

    public function __construct(VideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    public function index(Request $request)
    {
        $query = Video::query()->with('owner');
        $videos = $query->orderBy('created_at', 'desc')->paginate($request->input('pageSize', 10));
        return VideoResource::collection($videos);
    }

    public function show(Video $video)
    {
        return new VideoResource($video, true);
    }

    public function store(VideoRequest $request)
    {
        $video = $this->videoService->store($request);
        return new VideoResource($video, true);
    }

    public function update(VideoRequest $request, Video $video)
    {
        $video = $this->videoService->update($video, $request);
        return new VideoResource($video, true);
    }

    public function destroy(Video $video)
    {
        $user = Auth::user();
        if (!$user->can('delete', $video)) {
            abort(403);
        }
        $this->videoService->delete($video);
        return response()->json(['message' => 'Video deleted successfully.']);
    }

    public function storeComment(Request $request, Video $video)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $video->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        return response()->json($comment, 201);
    }

    public function action(Video $video, string $action)
    {
        // Allowed actions
        $allowedActions = ['favorite'];

        if (!in_array($action, $allowedActions)) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        $result = match ($action) {
            'favorite' => $this->videoService->favorite($video),
            default => response()->json(['message' => 'Invalid action'], 400)
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
