<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Favoritable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use Contactable;
    use Favoritable;
    use InteractsWithMedia;

    const IMAGES_FILES = "images_files";
    const FILES = "files_section";


    protected $fillable = [
        'title',
        'short_description',
        'description',
        'category',
        'format',
        'parameters',
        'owner_id',
        'date',
        'company',
        'is_archived',
    ];

    protected $casts = [
        'category' => 'array',
        'format' => 'array',
        'parameters' => 'array',
        'company' => 'array',
        'is_archived' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGES_FILES);
        $this->addMediaCollection(self::FILES);
    }

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }
}
