<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventApplication extends Model
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
        'event_id',
        'applicant_id',
        'status',
        'details'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function applicant()
    {
        return $this->belongsTo(Profile::class, 'applicant_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
