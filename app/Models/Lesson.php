<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Lesson
 * @property-read string $type
 * @property string      $type
 * @property string      $title
 * @property string      $description
 * @property string|null $video_link
 * @property string|null $external_link
 * @property string|null $address
 * @property string|null $speaker_first_name
 * @property string|null $speaker_last_name
 * @property string|null $speaker_bio
 * @property int|null    $course_id
 * @property int|null    $semester_id
 */
class Lesson extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    const SPEAKER_AVATAR_MEDIA = "speaker_avatar";

    protected $fillable = [
        'type',
        'title',
        'description',
        'video_link',
        'external_link',
        'address',
        'speaker_first_name',
        'speaker_last_name',
        'speaker_bio',
        'course_id',
        'semester_id',
    ];

    const VALIDATION_RULES = [
        'lessons.*.id' => 'nullable|integer',
        'lessons.*.type' => 'required|string',
        'lessons.*.title' => 'required|string',
        'lessons.*.description' => 'required|string',
        'lessons.*.video_link' => 'nullable|url',
        'lessons.*.external_link' => 'nullable|string',
        'lessons.*.address' => 'nullable|string',
        'lessons.*.avatar' => 'nullable|array',
        'lessons.*.speaker_first_name' => 'required|string',
        'lessons.*.speaker_last_name' => 'required|string',
        'lessons.*.speaker_bio' => 'required|string'
    ];

    const SEMESTER_VALIDATION_RULES = [
        'semesters.*.lessons.*.id' => 'nullable|integer',
        'semesters.*.lessons.*.type' => 'required|string',
        'semesters.*.lessons.*.title' => 'required|string',
        'semesters.*.lessons.*.description' => 'required|string',
        'semesters.*.lessons.*.video_link' => 'nullable|url',
        'semesters.*.lessons.*.external_link' => 'nullable|string',
        'semesters.*.lessons.*.address' => 'nullable|string',
        'semesters.*.lessons.*.avatar' => 'nullable|array',
        'semesters.*.lessons.*.speaker_first_name' => 'required|string',
        'semesters.*.lessons.*.speaker_last_name' => 'required|string',
        'semesters.*.lessons.*.speaker_bio' => 'required|string'
    ];

    public function scopeWithoutSemester(Builder $query): Builder
    {
        return $query->whereNull('semester_id');
    }
}
