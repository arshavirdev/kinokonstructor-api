<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'contactable_id', 'contactable_type', 'phone', 'email', 'website', 'socials', 'other'];

    protected $casts = [
        'phone' => 'array',
        'email' => 'array',
        'website' => 'array',
        'socials' => 'array',
        'other'=> 'array'
    ];

    public function contactable()
    {
        return $this->morphTo();
    }
}
