<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory;

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
