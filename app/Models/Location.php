<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Location extends AppModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const GALLERY_MEDIA = 'gallery';

    protected $casts = [
        'tags' => 'array'
    ];

    protected $fillable = ['name', 'description', 'owner_id', 'tags', 'region_id', 'city', 'latlng'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }

    public function scopeWhereHasTags($query, array $tags)
    {
        return $query->whereRaw('jsonb_exists_all(tags, (SELECT ARRAY(SELECT jsonb_array_elements_text(?))))', json_encode($tags));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::GALLERY_MEDIA)
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('large')
                    ->fit(Fit::Max, 1024, 1024)
                    ->quality(75)
                    ->optimize();
                $this
                    ->addMediaConversion('thumb')
                    ->fit(Fit::Max, 150, 150)
                    ->quality(70)
                    ->optimize();
                $this->addMediaConversion('preview')
                    ->fit(Fit::Max, 350, 350)
                    ->quality(75)
                    ->optimize();
            });
    }
}
