<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends AppModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const PICTURE_MEDIA = 'picture';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PICTURE_MEDIA)->singleFile()->registerMediaConversions(function (Media $media) {
            $this
                ->addMediaConversion('thumb')
                ->performOnCollections([self::PICTURE_MEDIA])
                ->fit(Manipulations::FIT_MAX, 100, 100)
                ->quality(75)
                ->optimize();
            $this
                ->addMediaConversion('medium')
                ->performOnCollections([self::PICTURE_MEDIA])
                ->fit(Manipulations::FIT_MAX, 400, 400)
                ->quality(75)
                ->optimize();
        });
    }
}
