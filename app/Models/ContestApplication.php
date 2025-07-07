<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestApplication extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_DECLINED = 'declined';
    const STATUS_ACCEPTED = 'accepted';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DECLINED,
        self::STATUS_ACCEPTED
    ];

    protected $fillable = [
        'contest_id',
        'applicant_id',
        'project_id',
        'description',
        'status'
    ];


    public function applicant()
    {
        return $this->belongsTo(Profile::class, 'applicant_id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
