<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Lesson
 * @property-read string $type
 * @property string      $type
 * @property string      $title
 * @property string      $description
 * @property string|null $video_link
 * @property string|null $address
 * @property string|null $speaker_first_name
 * @property string|null $speaker_last_name
 * @property string|null $speaker_bio
 * @property int|null    $course_id
 * @property int|null    $semester_id
 */
class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'video_link',
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
        'lessons.*.address' => 'nullable|string',
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
        'semesters.*.lessons.*.address' => 'nullable|string',
        'semesters.*.lessons.*.speaker_first_name' => 'required|string',
        'semesters.*.lessons.*.speaker_last_name' => 'required|string',
        'semesters.*.lessons.*.speaker_bio' => 'required|string'
    ];

    public function scopeWithoutSemester(Builder $query): Builder
    {
        return $query->whereNull('semester_id');
    }
}
