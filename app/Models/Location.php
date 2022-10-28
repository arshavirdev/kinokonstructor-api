<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Location extends AppModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $perPage = 5;

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
            ->withResponsiveImages();
    }
}
