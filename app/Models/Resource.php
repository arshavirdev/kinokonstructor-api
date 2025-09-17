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
 * @property bool $is_archived
 * @property string|null $external_link
 * @property-read \Date $created_at
 * @property-read \Date $updated_at
 */
class Resource extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Contactable;
    use Favoritable;

    const IMAGES_FILES = "images_files";
    const FILES = "files_section";

    protected $fillable = [
        "title",
        "description",
        "short_description",
        "category",
        "region_id",
        "owner_id",
        "parameters",
        "company",
        "is_archived",
        "request_id",
        'external_link'
    ];

    protected $casts = [
        "parameters" => "array",
        "company" => "array",
        "category" => "array",
        'is_archived' => 'bool',
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

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
