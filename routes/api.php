<?php

use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TokenController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

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


Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::get('', 'show');
    });

    Route::controller(VerifyEmailController::class)
        ->prefix('auth/email')
        ->middleware(['auth:sanctum'])->group(function () {
            Route::post('/verify/{id}/{hash}', 'verify');
            Route::post('/request-verify', 'request')->middleware(['throttle:1,1']);
        });

    Route::middleware(['verified'])->group(function () {
        Route::controller(UserController::class)->prefix('user')->group(function () {
            Route::post('profile', 'createProfile');
            Route::patch('profile', 'updateProfile');
        });

        Route::get('dictionaries/{dictionary?}', [DictionaryController::class, 'show']);
        Route::apiResource('profiles', ProfileController::class, ['only' => ['index', 'show']]);
        Route::apiResource('locations', LocationController::class);
        Route::apiResource('news', NewsController::class);
        Route::apiResource('posts', Post::class);

        Route::apiResource('projects', ProjectController::class);
        Route::controller(ProjectController::class)->prefix('projects')->group(function () {
            Route::post('/{project}/moderate', 'moderate');
        });
    });
});

