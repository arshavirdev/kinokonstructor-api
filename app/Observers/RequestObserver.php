<?php

namespace App\Observers;

use App\Models\Request;
use App\Models\Resource;

class RequestObserver
{
    public function created(Request $request)
    {
     
        $resource = Resource::create([
            'request_id' => $request->id,
            'title' => $request->names[0] ?? '',
            'owner_id' => $request->user_id,
            'short_description' => $request->info,
            'region_id' => 1, // TODO: update
            'description' => $request->info,
            'category' => $request->category,
        ]);
    

        $resource->clearMediaCollection(Resource::FILES);
        foreach ($request->getMedia(Request::DOCS_FILES) as $media) {
            $media->copy($resource, Request::DOCS_FILES);
        }

        $resource->clearMediaCollection(Resource::IMAGES_FILES);
        foreach ($request->getMedia(Request::IMAGES_FILES) as $media) {
            $media->copy($resource, Request::IMAGES_FILES);
        }
    }

    public function updated(Request $request)
    {
        
        $resource = $request->resource;

        if ($resource) {
            $resource->update([
                'title' => $request->names[0] ?? '',
                'owner_id' => $request->user_id,
                'short_description' => $request->info,
                'region_id' => 1, // TODO: update
                'description' => $request->info,
                'category' => $request->category,
            ]);

            $resource->clearMediaCollection(Resource::FILES);
            foreach ($request->getMedia(Request::DOCS_FILES) as $media) {
                $media->copy($resource, Request::DOCS_FILES);
            }

            $resource->clearMediaCollection(Resource::IMAGES_FILES);
            foreach ($request->getMedia(Request::IMAGES_FILES) as $media) {
                $media->copy($resource, Request::IMAGES_FILES);
            }
        }
    }

    public function deleted(Request $request)
    {
        $request->resource?->delete();
    }
}
