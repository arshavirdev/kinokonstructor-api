<?php

namespace App\Service\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class MediaPathGenerator implements PathGenerator
{

    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive/';
    }


    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $model_name = $media->model->getTable();
        $model_id = (string)$media->model_id;
        $collection_type = $media->collection_name;
        $path = $model_name . '/' . $model_id . '/' . $collection_type;
        $prefix = config('media-library.prefix', '');

        if ($prefix !== '') {
            $path = $prefix . '/' . $path;
        }

        return $path;
    }
}
