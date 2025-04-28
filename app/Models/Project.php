<?php

namespace App\Models;

use App\Traits\Favoritable;
use App\Traits\Moderation\Moderatable;
use App\Traits\Contactable;
use App\Traits\Requestable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property number applicant_id
 * @property Profile $applicant
 * @property Request[] $requests
 */
class Project extends AppModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Moderatable;
    use Favoritable;
    use Contactable;
    use Requestable;

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

    public const IMAGES = 'images';

    public const MEDIA_TYPES = [
        self::EXTENDED_SYNOPSIS_MEDIA, self::ATTACHMENTS_MEDIA,
        self::COSTUMES_MEDIA, self::MAKEUP_MEDIA,
        self::CAST_MEDIA, self::DECORATIONS_MEDIA,
        self::LOCATIONS_MEDIA, self::FINANCIAL_PLAN_MEDIA,
        self::FINANCIAL_PROOF_MEDIA, self::PARTNERSHIP_PROOF_MEDIA
    ];
    public const SYNOPSYS = 'synopsys';
    public const SCENARIO = 'scenario';
    public const DIRECTOR = 'director';
    public const PRODUCER = 'producer';
    public const ESTIMATE = 'estimate';
    public const PLAN = 'plan';

    public const MEDIA_FILE_TYPES = [
        self::SYNOPSYS,
        self::SCENARIO,
        self::DIRECTOR,
        self::PRODUCER,
        self::ESTIMATE,
        self::PLAN
    ];

    public const MEDIA_FILE_TYPES_MAPPING = [
        'synopsys' => self::SYNOPSYS,
        'scenario' => self::SCENARIO,
        'director' => self::DIRECTOR,
        'producer' => self::PRODUCER,
        'estimate' => self::ESTIMATE,
        'plan' => self::PLAN
    ];

    public static $validation = [
        'basic' => [
            "title" => "string",
            "format" => "in:movie,series",
            "short_description" => "string",
            "genre_type" => "in:documentary,fictional",
            "chronography" => "integer",
            "series_count" => "integer",
            "genres" => "array",
            "genres.*" => "integer",
            'privacy_hide' => 'array',
            'privacy_hide.*' => 'string|in:phone,email,website,socials',
        ],
        "additional" => [
            "detailed_description" => "string|max:500",
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
            "start_date" => "date",
            "end_date" => "date",
            "years_rating" => "string",
            "region_id" => "integer",
            "city" => "string",
            "profile_contacts" => "array",
            "requests" => "array",
            "files_section" => "array"
        ]
    ];

    protected $fillable = [
        "id",
        "owner_id",
        "title",
        "start_date",
        "end_date",
        "years_rating",
        "region_id",
        "city",
        "applicant_id",
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
        "is_archived",
        'privacy_hide'
    ];

    protected $casts = [
        'genres' => 'array',
        'members' => 'array',
        'custom_members' => 'array',
        'budget' => 'integer',
        'co_financing' => 'integer',
        'is_archived' => 'boolean',
        'privacy_hide' => 'array'
    ];

    public function memberInvites()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'project_locations');
    }

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function contact()
    {
        return $this->morphOne(Contact::class, 'contactable');
    }

    public function applicant()
    {
        return $this->belongsTo(Profile::class, 'applicant_id');
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

        // NEW
        $this->addMediaCollection(self::IMAGES);
    }
}
