<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Favoritable;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Class Course
 * @property string $title
 * @property string|null $description
 * @property int|null $duration
 * @property float|null $price
 * @property \DateTimeInterface|null $application_start_date
 * @property \DateTimeInterface|null $application_end_date
 * @property \DateTimeInterface|null $start_date
 * @property string|null $study_format
 * @property array $region_ids
 * @property int|null $owner_id
 * @property bool $is_archived
 */
class Course extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use Favoritable;
    use Contactable;

    const FILES_MEDIA = 'files_section';

    // study formats
    const FULL_TIME = 'full_time';
    const PART_TIME = 'part_time';
    const ONLINE = 'online';

    const MIXED = 'mixed';

    const STUDY_FORMATS = [
        self::FULL_TIME,
        self::PART_TIME,
        self::ONLINE,
        self::MIXED
    ];

    protected $fillable = [
        'title',
        'description',
        'duration',
        'price',
        'application_start_date',
        'application_end_date',
        'start_date',
        'study_format',
        'region_ids',
        'owner_id',
        'is_archived'
    ];

    protected $casts = [
        'price' => 'float',
        'duration' => 'integer',
        'region_ids' => 'array',
        'is_archived' => 'bool'
    ];

    public function owner()
    {
        return $this->belongsTo(Profile::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
