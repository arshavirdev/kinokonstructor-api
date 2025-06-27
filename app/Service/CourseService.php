<?php

namespace App\Service;

use App\DTOs\MediaSyncDataDTO;
use App\Models\Course;
use App\Http\Requests\StoreCourseRequest;
use App\Models\Lesson;
use App\Models\Semester;
use App\Models\Teacher;
use App\Service\Media\MediaService;
use App\Service\Shared\ContactHandlerService;
use Illuminate\Support\Facades\Auth;

class CourseService
{
    function __construct(
        private ContactHandlerService $contactHandlerService,
        private MediaService $mediaService
    ) {
    }

    public function store(StoreCourseRequest $request): Course
    {
        $authUser = auth()->user();
        $data = $request->validated();
        $data['owner_id'] = $authUser->profile->id;

        $course = Course::create($data);


        if ($request->has('semesters')) {
            $lessonsAvatarsMediaDto = new MediaSyncDataDTO(
                $request->file('semesters', []),
                $request->post('semesters', [])
            );
            $this->handleSemesters($course, $data['semesters'], $lessonsAvatarsMediaDto);
        }

        if ($request->has('teachers')) {
            $this->handleTeachers($course, $request);
        }

        if ($request->has('lessons')) {
            $avatarsMediaDto = new MediaSyncDataDTO(
                $request->file('lessons', []),
                $request->post('lessons', [])
            );
            $this->handleCourseLessons($course, $data['lessons'], $avatarsMediaDto);
        }

        // Handle media files
        $this->handleCourseMedia($course, $request);

        return $course;
    }

    public function update(Course $course, StoreCourseRequest $request): Course
    {
        $authUser = auth()->user();
        $data = $request->validated();
        $course->update($data);
        $data['owner_id'] = $authUser->profile->id;

        if ($request->has('teachers')) {
            $this->handleTeachers($course, $request);
        }

        if ($request->has('lessons')) {
            $avatarsMediaDto = new MediaSyncDataDTO(
                $request->file('lessons', []),
                $request->post('lessons', [])
            );
            $this->handleCourseLessons($course, $data['lessons'], $avatarsMediaDto);
        }

        if ($request->has('semesters')) {
             $lessonsAvatarsMediaDto = new MediaSyncDataDTO(
                $request->file('semesters', []),
                $request->post('semesters', [])
            );
            $this->handleSemesters($course, $data['semesters'], $lessonsAvatarsMediaDto);
        }

        // Handle media files
        $this->handleCourseMedia($course, $request);

        return $course;
    }

    public function delete(Course $course): bool
    {
        return $course->delete();
    }

    function handleTeachers(Course $course, StoreCourseRequest $request)
    {
        $teachersMedia = $request->file('teachers');
        $teachers = $request->get('teachers', []);

        $teacherIds = [];
        $existingTeacherIds = array_filter(array_column($teachers, 'id'));

        $existingTeachers = Teacher::whereIn('id', $existingTeacherIds)
            ->get()
            ->keyBy('id');

        foreach ($teachers as $index => $teacherData) {
            if (!empty($teacherData['id']) && $existingTeachers->has($teacherData['id'])) {
                $teacher = $existingTeachers[$teacherData['id']];
                $teacher->update($teacherData);
                $teacherIds[] = $teacherData['id'];
            } else {
                $teacher = Teacher::create($teacherData);
                $teacherIds[] = $teacher->id;
            }

            $mediaFilesDto = new MediaSyncDataDTO(
                $teachersMedia[$index][Teacher::AVATAR_MEDIA] ?? [],
                $teacherData[Teacher::AVATAR_MEDIA] ?? [],
            );

            // Store/Update teacher avatar
            $this->mediaService->syncMediaCollection($teacher, $mediaFilesDto, Teacher::AVATAR_MEDIA);
        }

        $course->teachers()->sync($teacherIds);
    }

    function handleSemesterLessons(Semester $semester, array $lessons, MediaSyncDataDTO $lessonsMediaDTO)
    {
        $existingLessonIds = array_filter(array_column($lessons, 'id'));
        $existingLessons = Lesson::whereIn('id', $existingLessonIds)
            ->get()
            ->keyBy(keyBy: 'id');

        // Get lessons to delete
        $lessonsToDelete = $semester->lessons()
            ->when(!empty($existingLessonIds), fn($q) => $q->whereNotIn('id', $existingLessonIds))
            ->where('semester_id', $semester->id)
            ->get();

        // Delete course lessons
        if ($lessonsToDelete->isNotEmpty()) {
            $semester->lessons()
                ->whereIn('id', $lessonsToDelete->pluck('id'))
                ->delete();
        }

        foreach ($lessons as $index => $lessonData) {
            $lessonData['course_id'] = $semester->course_id;
            $lessonData['semester_id'] = $semester->id;
            $lessonMediaDto = new MediaSyncDataDTO(
                $lessonsMediaDTO->files[$index][Lesson::SPEAKER_AVATAR_MEDIA] ?? [],
                $lessonsMediaDTO->post[$index][Lesson::SPEAKER_AVATAR_MEDIA] ?? [],
            );

            if (!empty($lessonData['id']) && $existingLessons->has($lessonData['id'])) {
                $lesson = $existingLessons[$lessonData['id']];
                $lesson->update($lessonData);
            } else {
                $lesson = Lesson::create($lessonData);
            }

            $this->mediaService->syncMediaCollection($lesson, $lessonMediaDto, Lesson::SPEAKER_AVATAR_MEDIA);
        }
    }

    function handleCourseLessons(Course $course, array $lessons, MediaSyncDataDTO $mediaSyncDataDTO)
    {
        $existingLessonIds = array_filter(array_column($lessons, 'id'));
        $existingLessons = Lesson::whereIn('id', $existingLessonIds)
            ->get()
            ->keyBy('id');

        // Get course lessons to delete
        $lessonsToDelete = $course->lessons()
            ->when(!empty($existingLessonIds), fn($q) => $q->whereNotIn('id', $existingLessonIds))
            ->withoutSemester()
            ->get();

        // Delete course lessons
        if ($lessonsToDelete->isNotEmpty()) {
            $course->lessons()
                ->whereIn('id', $lessonsToDelete->pluck('id'))
                ->delete();
        }

        foreach ($lessons as $index => $lessonData) {
            $lessonMediaDto = new MediaSyncDataDTO(
                $mediaSyncDataDTO->files[$index][Lesson::SPEAKER_AVATAR_MEDIA] ?? [],
                $mediaSyncDataDTO->post[$index][Lesson::SPEAKER_AVATAR_MEDIA] ?? [],
            );

            $lessonData['course_id'] = $course->id;
            if (!empty($lessonData['id']) && $existingLessons->has($lessonData['id'])) {
                $lesson = $existingLessons[$lessonData['id']];
                $lesson->update($lessonData);
            } else {
                $lesson = Lesson::create($lessonData);
            }

            $this->mediaService->syncMediaCollection($lesson, $lessonMediaDto, Lesson::SPEAKER_AVATAR_MEDIA);
        }
    }

    function handleSemesters(Course $course, array $semesters, MediaSyncDataDTO $semestersMediaSyncDataDTO)
    {
        $existingSemesterIds = array_filter(array_column($semesters, 'id'));
        $existingSemesters = Semester::whereIn('id', $existingSemesterIds)
            ->get()
            ->keyBy('id');

        // Get semesters to delete
        $semestersToDelete = $course->semesters()
            ->when(!empty($existingSemesterIds), function ($query) use ($existingSemesterIds) {
                $query->whereNotIn('id', $existingSemesterIds);
            })
            ->get();

        // TODO: improve
        // Delete semester lessons first
        foreach ($semestersToDelete as $semester) {
            $semester->lessons()->delete();
        }

        // Delete semesters
        if ($semestersToDelete->isNotEmpty()) {
            $course->semesters()->whereIn('id', $semestersToDelete->pluck('id'))->delete();
        }

        foreach ($semesters as $index => $semesterData) {
            $semesterData['course_id'] = $course->id;
            $semesterId = $semesterData['id'] ?? null;
            $lessonsMediaDTO = new MediaSyncDataDTO(
                $semestersMediaSyncDataDTO->files[$index]['lessons'] ?? [],
                $semestersMediaSyncDataDTO->post[$index]['lessons'] ?? []
            );

            if (!empty($semesterData['id']) && $existingSemesters->has($semesterData['id'])) {
                $semester = $existingSemesters[$semesterId];
                $semester->update($semesterData);
            } else {
                $semester = Semester::create($semesterData);
                $semesterId = $semester->id;
            }

            // Handle semester lessons
            if (!empty($semesterData['lessons'])) {
                $this->handleSemesterLessons($semester, $semesterData['lessons'], $lessonsMediaDTO);
            }
        }
    }

    /**
     * HandleCourseMedia
     * @param \App\Models\Course $course
     * @param \App\Http\Requests\StoreCourseRequest $request
     * @return void
     */
    function handleCourseMedia(Course $course, StoreCourseRequest $request)
    {
        $filesMediaDto = new MediaSyncDataDTO(
            $request->file(Course::FILES_MEDIA, []),
            $request->post(Course::FILES_MEDIA, [])
        );
        $this->mediaService->syncMediaCollection($course, $filesMediaDto, Course::FILES_MEDIA);
    }

    /**
     * Favorite/Unfavorite
     * @param \App\Models\Course $course
     * @return array{is_favorite: bool}
     */
    public function favorite(Course $course)
    {
        $userId = Auth::id();
        $exists = $course->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $course->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $course->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    /**
     * Archive
     * @param \App\Models\Course $course
     * @return array{is_archived: bool}
     */
    public function archive(Course $course)
    {
        if ($course->update(['is_archived' => true])) {
            return ['is_archived' => true];
        }

        return ['is_archived' => false];
    }

    /**
     * Unarchive
     * @param \App\Models\Course $course
     * @return array{is_archived: bool}
     */
    public function unarchive(Course $course)
    {
        if ($course->update(['is_archived' => false])) {
            return ['is_archived' => false];
        }

        return ['is_archived' => true];
    }
}
