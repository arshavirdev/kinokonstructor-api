<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Service\VideoService;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(private VideoService $videoService)
    {

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
        $this->authorize( 'update', $video);

        $video = $this->videoService->update($video, $request);
        return new VideoResource($video, true);
    }

    public function destroy(Video $video)
    {
        $this->authorize( 'delete', $video);

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

        return new CommentResource($comment);
    }

    public function action(Video $video, string $action)
    {
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
