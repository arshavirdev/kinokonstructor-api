<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Semester;
use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'study_format' => 'required|string|in:' . implode(',', Course::STUDY_FORMATS),
            'region_ids' => 'required|array',
            'start_date' => 'required|date',
            'application_start_date' => 'required|date',
            'application_end_date' => 'required|date',

            // Semesters
            'semesters' => 'nullable|array',
            ...Semester::VALIDATION_RULES,

            // Lessons
            'lessons' => 'nullable|array',
            ...Lesson::VALIDATION_RULES,

            // Teachers
            'teachers' => 'nullable|array',
            ...Teacher::VALIDATION_RULES,

            Course::FILES_MEDIA => "nullable|array",
        ];
    }
}
