<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\RegionalBranchController;
use App\Http\Controllers\BranchMemberController;
use App\Http\Controllers\BranchNewsController;
use App\Http\Controllers\ContestApplicationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TokenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$verificationLimiterMiddleware = 'throttle:' . config('fortify.limiters.verification', '6,1');

Route::post('/sanctum/token', TokenController::class);


Route::get('/project/invite/accept', [InviteController::class, 'acceptInvite']);
Route::get('/project/invite/reject', [InviteController::class, 'rejectInvite']);

Route::post('/auth/checkId', [MemberController::class, 'checkId']);

// PUBLIC ROUTES
Route::get('dictionaries/{dictionary?}', [DictionaryController::class, 'show']);
Route::apiResource('/videos', VideoController::class)->only(['show', 'index']);
Route::apiResource('/contests', ContestController::class)->only(['show', 'index']);
Route::apiResource('/regional-branches', RegionalBranchController::class)->only(['show', 'index']);
Route::apiResource('/resources', ResourceController::class)->only(['show', 'index']);
Route::apiResource('/events', EventController::class)->only(['index', 'show']);
Route::apiResource('/courses', CourseController::class)->only(['index', 'show']);
Route::apiResource('/lessons', LessonController::class)->only('show');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('auth/user/password', [PasswordController::class, 'update']);
    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::get('', 'showCurrentUser');
    });

    Route::controller(TokenController::class)->prefix('auth')->group(function () {
        Route::post('impersonate', 'impersonate');
        Route::post('unimpersonate', 'unimpersonate');
    });

    Route::controller(VerifyEmailController::class)
        ->prefix('auth/email')
        ->group(function () {
            Route::post('/verify/{id}/{hash}', 'verify');
            Route::post('/request-verify', 'request')->middleware(['throttle:verify-email']);
        });

    Route::middleware(['verified'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::controller(UserController::class)->prefix('user')->group(function () {
            Route::post('profile', 'createProfile');
            Route::post('profile-settings', 'createProfileSettings');

            Route::patch('profile', 'updateProfile');
            Route::patch('profile-settings', 'updateProfileSettings');

            Route::delete('profile', 'deleteProfile');
        });

        Route::apiResource('profiles', ProfileController::class, ['only' => ['index', 'show']]);
        Route::apiResource('locations', LocationController::class);

        // CONTEST
        Route::apiResource('contests', ContestController::class)->except(['index', 'show']);
        Route::post('/contests/{contest}/apply', [ContestApplicationController::class, 'apply']);
        Route::post('/contests/{contest}/{action}', [ContestController::class, 'action'])
            ->where('action', 'favorite|archive|unarchive');

        // CONTEST APPLICATION
        Route::get('contests-applications', [ContestApplicationController::class, 'index']);
        Route::get('contests-applications/{contestApplication}', [ContestApplicationController::class, 'show']);

        // PROJECT
        Route::apiResource('projects', ProjectController::class);
        Route::get('/application-projects', [ProjectController::class,'forApplication']);
        Route::prefix('projects/{project}')->group(function () {
            Route::post('/moderate', [ProjectController::class, 'moderate']);
            Route::controller(ProjectController::class)->prefix('moderation')->group(function () {
                Route::post('/cancel', 'cancelModeration');
                Route::post('/approve', 'approveModeration');
                Route::post('/reject', 'rejectModeration');
            });

            Route::post('/invite', [InviteController::class, 'inviteMember']);
            Route::post('/remove/{project_member}', [InviteController::class, 'removeMember']);

            Route::get('/locations', [ProjectController::class, 'indexLocations']);
            Route::post('/locations/add', [ProjectController::class, 'addLocation']);
            Route::post('/locations/remove', [ProjectController::class, 'removeLocation']);

            Route::post('/export', [\App\Actions\Project\ExportProject::class, 'export']);

            Route::post('/{action}', [ProjectController::class, 'action'])
                ->where('action', 'favorite|archive|unarchive');
        });

        // REPORT
        Route::post('/report', [ReportController::class, 'store']);
    });
    Route::middleware(['moderator'])->group(function () {
        Route::apiResource('users', UserAdminController::class);
        Route::controller(UserAdminController::class)->prefix('users/{user}/')->group(function () {
            Route::post('verify', 'markAsVerified');
            Route::post('unverify', 'markAsUnverified');
            Route::post('set_role', 'setRole');
        });
        Route::controller(UserAdminController::class)->prefix('profiles/{profile}')->group(function () {
            Route::post('approve', 'approveProfile');
            Route::post('reject', 'rejectProfile');
        });
    });

    // NOTIFICATION
    Route::prefix('notifications')->group(function () {
        Route::get('', [NotificationController::class, 'index']);
        Route::get('test', [NotificationController::class, 'test']);
        Route::post('/read', [NotificationController::class, 'markAsRead']);
    });

    // CONTACT ORGANIZER
    Route::post('/organizer/contact', [UserController::class, 'contactOrganizer']);

    // VIDEO
    Route::apiResource('/videos', VideoController::class)->except(['show', 'index']);
    Route::post('/videos/{video}/comments', [VideoController::class, 'storeComment']);
    Route::post('/videos/{video}/{action}', [VideoController::class, 'action'])
        ->where('action', 'favorite');

    // REGIONAL BRANCH
    Route::apiResource('/regional-branches', RegionalBranchController::class)->except(['show', 'index']);
    ;

    // BRANCH MEMBERS AND NEWS
    Route::prefix('/regional-branches/{regionalBranch}')->group(function () {
        Route::apiResource('/members', BranchMemberController::class)->only(['store']);
        Route::apiResource('/news', BranchNewsController::class)->only(['store']);
    });

    // RESOURCE
    Route::apiResource('/resources', ResourceController::class)->except(['index', 'show']);
    Route::post('/resources/{resource}/{action}', [ResourceController::class, 'action'])
        ->where('action', 'favorite|archive|unarchive');

    // VACANCY
    Route::apiResource('/vacancies', VacancyController::class);
    Route::post('/vacancies/{vacancy}/{action}', [VacancyController::class, 'action'])
        ->where('action', 'favorite|archive|unarchive');

    // RESUME
    Route::apiResource('/resumes', ResumeController::class);
    Route::post('/resumes/{resume}/{action}', [ResumeController::class, 'action'])
        ->where('action', 'favorite|archive|unarchive');
    
    // EVENT
    Route::apiResource('/events', EventController::class)->except(['index', 'show']);
    Route::post('/events/{event}/{action}', [EventController::class, 'action'])
        ->where('action', 'favorite|archive|unarchive');

    // COURSE
    Route::apiResource('/courses', CourseController::class)->except(['index', 'show']);
    Route::post('/courses/{course}/{action}', [CourseController::class, 'action'])
        ->where('action', 'favorite|archive|unarchive');
});
