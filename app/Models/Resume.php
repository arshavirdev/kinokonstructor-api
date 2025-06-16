<?php

namespace App\Models;

use App\Traits\Contactable;
use App\Traits\Favoritable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    use HasFactory;
    use Favoritable;
    use Contactable;

    const WORK_FORMAT_OFFICE = 'office';
    const WORK_FORMAT_REMOTE = 'remote';
    const WORK_FORMAT_ANY = 'any';
    const FORMATS = [
        self::WORK_FORMAT_OFFICE,
        self::WORK_FORMAT_REMOTE,
        self::WORK_FORMAT_ANY
    ];

    protected $casts = [
        'experience_years' => 'int'
    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'position_id',
        'work_format',
        'location',
        'experience_years',
        'experience_description',
        'owner_id',
        'salary_expectation',
        'bio',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Occupation::class, 'position_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }
}
