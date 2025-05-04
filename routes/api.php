<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\VideoController;
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


Route::middleware(['auth:sanctum'])->group(function () {
    Route::put('auth/user/password', [PasswordController::class,'update']);
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
            Route::patch('profile', 'updateProfile');
        });

        Route::get('dictionaries/{dictionary?}', [DictionaryController::class, 'show']);
        Route::apiResource('profiles', ProfileController::class, ['only' => ['index', 'show']]);
        Route::apiResource('locations', LocationController::class);
        Route::apiResource('contests', ContestController::class);
        Route::post('/contests/{contest}/{action}', [ContestController::class, 'action'])
                ->where('action', 'favorite|archive|unarchive');

        Route::apiResource('projects', ProjectController::class);
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

        // VIDEO
        Route::apiResource('/videos', VideoController::class);
        Route::post('/videos/{video}/comments', [VideoController::class, 'storeComment']);
        Route::post('/videos/{video}/{action}', [VideoController::class, 'action'])
            ->where('action', 'favorite');
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
        Route::patch('{id}/read', [NotificationController::class, 'markAsRead']);
    });

    // CONTACT ORGANIZER
    Route::post('/organizer/contact', [UserController::class, 'contactOrganizer']);
});
