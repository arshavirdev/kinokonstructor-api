<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'website',
        'social_media',
        'email',
        'phone',
        'postal_address',
        'button_name',
    ];
}
