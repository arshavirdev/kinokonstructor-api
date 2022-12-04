<?php

namespace App\Models;

use App\Traits\Moderation\Moderatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends AppModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Moderatable;

    public const EXTENDED_SYNOPSIS_MEDIA = 'extended_synopsis';
    public const ATTACHMENTS_MEDIA = 'attachments';
    public const COSTUMES_MEDIA = 'costumes';
    public const MAKEUP_MEDIA = 'makeup';
    public const CAST_MEDIA = 'cast_reference';
    public const DECORATIONS_MEDIA = 'decorations';
    public const LOCATIONS_MEDIA = 'location_reference';
    public const FINANCIAL_PLAN_MEDIA = 'financial_plan';
    public const FINANCIAL_PROOF_MEDIA = 'financial_proof';
    public const PARTNERSHIP_PROOF_MEDIA = 'partnership_proof';

    public static $validation = [
        'basic' => [
            "title" => "string",
            "format" => "in:movie,series",
            "genre_type" => "in:documentary,fictional",
            "chronography" => "integer",
            "series_count" => "integer",
            "genres" => "array",
            "genres.*" => "integer",
        ],
        "additional" => [
            "logline" => "string",
            "synopsis" => "string",
            "extended_synopsis" => "nullable",
            "relevance" => "string",
            "additional" => "string",
            "attachments.*" => "nullable",
            "costumes" => "nullable",
            "makeup" => "nullable",
            "decorations" => "nullable",
            "cast_reference" => "nullable",
            "audio_reference" => "string",
            "location_reference" => "nullable",
            "budget" => "integer",
            "co_financing" => "integer",
            "financial_plan" => "nullable",
            "financial_proof" => "nullable",
            "partners" => "array",
            "partnership_proof" => "nullable",
            "custom_members" => "array",
            "members" => "array",
        ]
    ];

    protected $fillable = [
        "id",
        "owner_id",
        "title",
        "format",
        "genre_type",
        "chronography",
        "series_count",
        "genres",
        "logline",
        "synopsis",
        "relevance",
        "additional",
        "audio_reference",
        "budget",
        "co_financing",
        "custom_members",
    ];

    protected $casts = [
        'genres' => 'array',
        'members' => 'array',
        'custom_members' => 'array',
        'budget' => 'integer',
        'co_financing' => 'integer',
    ];

    public function memberInvites()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'project_locations');
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::EXTENDED_SYNOPSIS_MEDIA)->singleFile();
        $this->addMediaCollection(self::ATTACHMENTS_MEDIA);
        $this->addMediaCollection(self::COSTUMES_MEDIA)->singleFile();
        $this->addMediaCollection(self::MAKEUP_MEDIA)->singleFile();
        $this->addMediaCollection(self::CAST_MEDIA)->singleFile();
        $this->addMediaCollection(self::DECORATIONS_MEDIA)->singleFile();
        $this->addMediaCollection(self::LOCATIONS_MEDIA)->singleFile();
        $this->addMediaCollection(self::FINANCIAL_PLAN_MEDIA)->singleFile();
        $this->addMediaCollection(self::FINANCIAL_PROOF_MEDIA)->singleFile();
        $this->addMediaCollection(self::PARTNERSHIP_PROOF_MEDIA);
    }
}
