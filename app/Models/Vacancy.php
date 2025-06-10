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

    protected $fillable = [
        "format",
        "position_id",
        "description",
        "owner_id",
    ];

    public function owner()
    {
        return $this->belongsTo(Profile::class, 'owner_id');
    }
}
