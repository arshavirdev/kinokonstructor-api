<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Favoritable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Summary of Vacancy
 * @property string $format
 * @property number $position_id
 * @property string $description
 * @property string $salary_range
 * @property string $experience_required
 * @property-read \Date $created_at
 * @property-read \Date $updated_at
 */
class Vacancy extends Model
{
    use HasFactory;
    use Favoritable;
    use Contactable;

    const REMOTE = 'remote';
    const OFFICE = 'office';
    const BUSINESS_TRIP = 'business_trip';

    const FULL_TIME = 'full_time';
    const PART_TIME = 'part_time';
    const PROJECT_BASED = 'project_based';

    const FORMATS = [
        self::REMOTE,
        self::OFFICE,
        self::BUSINESS_TRIP
    ];

    const EMPLOYMENT_TYPES = [
        self::FULL_TIME,
        self::PART_TIME,
        self::PROJECT_BASED
    ];

    protected $fillable = [
        'format',
        'position_id',
        'description',
        'owner_id',
        'employment_type',
        'department_id',
        'region_id',
        'salary',
        'experience',
        'is_experience_required',
        'company',
    ];

    protected $casts = [
        'company' => 'array',
        'is_experience_required' => 'boolean'
    ];

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }
}
