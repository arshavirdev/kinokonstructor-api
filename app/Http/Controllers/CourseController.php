<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseBriefResource;
use App\Http\Resources\LessonResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Service\CourseService;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Resources\CourseResource;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends Controller
{
    function __construct(private CourseService $courseService)
    {
    }

    /**
     * Display a listing of the course.
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user?->id;
        $profileId = $user?->profile?->id;

        $query = Course::query()->with(['owner'])
            ->withCount([
                'favorites as is_favorite' => fn($q) => $q->where('user_id', $userId),
            ]);

        $query->when($request->get('type') === 'my', function ($q) use ($profileId) {
            $q->where('owner_id', $profileId);
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where('title', 'like', '%' . $request->get('search') . '%');
        });

        $query->when(filter_var($request->input('favorite'), FILTER_VALIDATE_BOOLEAN), function ($q) use ($profileId) {
            $q->whereHas('favorites', fn($subQ) => $subQ->where('owner_id', $profileId));
        });

        if ($request->has('filter.region_ids')) {
        $regionIds = collect(request('filter.region_ids'))->flatten()
            ->filter()
            ->map(fn($id) => (int)$id)
            ->toArray();

            $query->where(function ($query) use ($regionIds) {
                collect($regionIds)->map(fn($locationId) =>
                    $query->orWhereJsonContains('region_ids', $locationId)
                );
            });
        }
        $query->when($request->filled('study_format'), function ($q) use ($request) {
            $q->where('study_format', $request->input('study_format'));
        });

        $query->when($request->filled('duration'), function ($q) use ($request) {
            $q->where('duration', $request->input('duration'));
        });

        $resources = $query->orderBy('created_at', 'DESC')
            ->paginate($request->input('pageSize', 10));
        return CourseBriefResource::collection($resources);
    }

    /**
     * Store a newly created course in storage.
     *
     * @param  StoreCourseRequest  $request
     * @return CourseResource
     */
    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->store($request);
        $course->load(['teachers', 'media', 'lessons', 'semesters.lessons']);
        return new CourseResource($course);
    }

    /**
     * Display the specified course.
     *
     * @param  \App\Models\Course  $course
     * @return CourseResource
     */
    public function show(Course $course)
    {
        return new CourseResource($course);
    }

    /**
     * Update the specified course in storage.
     *
     * @param  StoreCourseRequest  $request
     * @param  \App\Models\Course  $course
     * @return CourseResource
     */
    public function update(StoreCourseRequest $request, Course $course)
    {
        $course = $this->courseService->update($course, $request);
        $course->load(['teachers', 'media', 'lessons', 'semesters.lessons']);
        return new CourseResource($course);
    }

    /**
     * Remove the specified course from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        $this->courseService->delete($course);
        return response()->noContent();
    }

    public function getLesson(Lesson $lesson)
    {
        return new LessonResource($lesson);
    }

    public function action(Course $course, string $action)
    {
        $result = match ($action) {
            'favorite' => $this->courseService->favorite($course),
            'archive' => $this->courseService->archive($course),
            'unarchive' => $this->courseService->unarchive($course),
            default => ['error' => 'Invalid action']
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
