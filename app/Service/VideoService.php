<?php

namespace App\Service;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoService
{
    public function store(Request $request): Video
    {
        $data = $request->all();
        $data['owner_id'] = Auth::id();

        $video = Video::create($data);

        if ($request->hasFile('video_file')) {
            $videoFile = $request->file('video_file');
            $video->addMedia($videoFile)->toMediaCollection(Video::VIDEO_FILE);
        }

        return $video;
    }

    public function update(Video $video, Request $request): Video
    {
        $data = $request->except('video_file');
        $video->update($data);

        if ($request->hasFile('video_file')) {
            $video->clearMediaCollection(Video::VIDEO_FILE);
            $videoFile = $request->file('video_file');
            $video->addMedia($videoFile)->toMediaCollection(Video::VIDEO_FILE);
        }

        return $video;
    }

    public function delete(Video $video): bool
    {
        $video->clearMediaCollection(Video::VIDEO_FILE);
        return $video->delete();
    }

    public function favorite(Video $video)
    {
        $userId = Auth::id();
        $exists = $video->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $video->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $video->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }
}
