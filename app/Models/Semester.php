<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'course_id'
    ];

    const VALIDATION_RULES = [
        'semesters.*.id' => 'nullable|integer',
        'semesters.*.description' => 'required|string',
        'semesters.*.start_date' => 'required|date',
        'semesters.*.end_date' => 'required|date',
        'semesters.*.lessons' => 'nullable|array',
        ...Lesson::SEMESTER_VALIDATION_RULES
    ];

    function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
