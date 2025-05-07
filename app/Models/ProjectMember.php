<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory;

    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    const FILTER_ROLES_IDS = [
        92, // Сценарист
        109, //Продюсер
        103 // Режиссер
    ];

    protected $fillable = [
        'project_id', 'profile_id', 'role', 'type', 'invitation_code', 'data'
    ];
    protected $casts = [
        'data' => 'array'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
