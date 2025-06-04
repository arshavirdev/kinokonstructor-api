<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Favoritable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Resource Model
 * @property string $title
 * @property string $short_description
 * @property string $description
 * @property int $region_id
 * @property int $owner_id
 * @property array $category
 * @property array $parameters
 * @property array $company
 */
class Resource extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Contactable;
    use Favoritable;

    const IMAGES_FILES = "images_files";

    protected $fillable = [
        "title",
        "description",
        "short_description",
        "category",
        "region_id",
        "owner_id",
        "parameters",
        "company"
    ];

    protected $casts = [
        "parameters" => "array",
        "company" => "array",
        "category" => "array"
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGES_FILES);
    }

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }
}
