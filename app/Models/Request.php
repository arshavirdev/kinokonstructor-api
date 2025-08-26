<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Request extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    const LOCATION_REQUEST =  'location';
    const EQUIPMENT_REQUEST =  'equipment';
    const SPECIFICATION_REQUEST =  'specification';
    const SERVICES_REQUEST =  'services';
    const OTHER_REQUEST =  'other';

    const DOCS_FILES = 'docs_files';
    const IMAGES_FILES = 'images_files';

    const REQUEST_TYPES = [
        self::LOCATION_REQUEST,
        self::EQUIPMENT_REQUEST,
        self::SPECIFICATION_REQUEST,
        self::OTHER_REQUEST,
        self::SERVICES_REQUEST

    ];

    protected $fillable = [
        'user_id',
        'requestable_id',
        'requestable_type',
        'name',
        'location',
        'season',
        'category',
        'info',
        'type'
    ];

    protected $casts = [
        'season' => 'array',
        'category' => 'array'
    ];

    public function resource()
    {
        return $this->hasOne(Resource::class);
    }

    public function requestable()
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::DOCS_FILES);
        $this->addMediaCollection(self::IMAGES_FILES);
    }
}
