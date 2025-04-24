<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    const REQUEST_TYPE_MAPPING = [
        'location_requests' => 'location',
        'equipment_requests' => 'equipment',
        'specialists_requests' => 'specialist',
        'other_requests' => 'other',
    ];

    protected $fillable = [
        'user_id',
        'requestable_id',
        'requestable_type',
        'name',
        'location',
        'season',
        'info',
        'type'
    ];

    public function requestable()
    {
        return $this->morphTo();
    }
}
