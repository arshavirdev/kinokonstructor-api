<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BranchNews extends Model implements HasMedia
{
    use InteractsWithMedia;

    const string IMAGE = 'image';

    protected $fillable = [
        'title',
        'short_description',
        'description',
        'category'
    ];

    protected $casts = [
        'category' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGE)
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this
                    ->addMediaConversion('thumb')
                    ->performOnCollections([self::IMAGE])
                    ->fit(Fit::Max, 100, 100)
                    ->quality(75)
                    ->optimize();
                $this
                    ->addMediaConversion('large')
                    ->performOnCollections([self::IMAGE])
                    ->fit(Fit::Max, 1024, 400)
                    ->quality(75)
                    ->optimize();
            });

    }
}
