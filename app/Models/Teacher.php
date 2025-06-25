<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Teacher
 * @property-read int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $bio
 */
class Teacher extends Model implements HasMedia
{
    use HasFactory;

    use InteractsWithMedia;

    const AVATAR_MEDIA = 'avatar';

    public $timestamps = false;

    protected $fillable = [
        'first_name',
        'last_name',
        'bio'
    ];

    const VALIDATION_RULES = [
        "teachers.*.id" => "nullable|integer",
        "teachers.*.first_name" => "required|string",
        "teachers.*.last_name" => "required|string",
        "teachers.*.bio" => "nullable|string",
        "teachers.*." . Teacher::AVATAR_MEDIA => "nullable|array",
    ];
}
