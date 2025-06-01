<?php

namespace App\Service\Media;

use Log;
use App\DTOs\MediaSyncDataDTO;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;

class MediaService
{
    /**
     * @param \Spatie\MediaLibrary\HasMedia $model
     * @param MediaSyncDataDTO $dto
     * @param string $collectionName
     * @return void
     */
    public function syncMediaCollection(HasMedia $model, MediaSyncDataDTO $dto, string $collectionName): void
    {
        $mediaIdsToKeep = collect($dto->post)
            ->pluck('id')
            ->filter()
            ->toArray();

        //  Delete all media files exclude media IDs form req.body
        // TODO: improve if needs
        $model->getMedia($collectionName)
            ->reject(fn($media) => in_array($media->id, $mediaIdsToKeep))
            ->each->delete();

        foreach ($dto->files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $model->addMedia($file)->toMediaCollection($collectionName);
                } catch (\Throwable $e) {
                    Log::error('Media upload failed', [
                        'id' => $model->id,
                        'model' => \get_class($model),
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::warning('Invalid file type:', [
                    'id' => $model->id,
                    'model' => \get_class($model),
                    'type' => \gettype($file),
                ]);
            }
        }
    }
}