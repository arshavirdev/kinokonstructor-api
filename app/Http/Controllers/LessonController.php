<?php

namespace App\Http\Controllers;

use App\Http\Resources\LessonResource;
use App\Models\Lesson;

class LessonController extends Controller
{

    /**
     * Display the specified lesson.
     *
     * @param  \App\Models\Lesson  $lesson
     * @return LessonResource
     */
    public function show(Lesson $lesson)
    {
        return new LessonResource($lesson);
    }
}
