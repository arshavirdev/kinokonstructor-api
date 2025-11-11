<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Favoritable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Event
 * 
 * @property int $id
 * @property string $title
 * @property string $short_description
 * @property string $description
 * @property array $category
 * @property array $format
 * @property array $parameters
 * @property string|null $location
 * @property int $owner_id
 * @property \Illuminate\Support\Carbon|string $date
 * @property array $company
 * @property bool $is_archived
 * @property-read \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Support\Carbon $updated_at
 * @property array $region_ids
 */
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
        'location',
        'owner_id',
        'date',
        'company',
        'is_archived',
        'is_recorded',
        'external_link',
        'region_ids'
    ];

    protected $casts = [
        'category' => 'array',
        'format' => 'array',
        'parameters' => 'array',
        'company' => 'array',
        'is_archived' => 'boolean',
        'is_recorded' => 'boolean',
        'region_ids' => 'array'
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

    public function feedbacks()
    {
        return $this->morphMany(Feedback::class, 'feedbackable');
    }
}
