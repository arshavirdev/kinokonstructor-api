<?php

namespace App\DTOs;
use Illuminate\Http\UploadedFile;

class MediaSyncDataDTO
{
    public function __construct(
        /** @var (string|UploadedFile)[] */
        public array $files = [],
        public array $post = []
    ) {
    }
}
