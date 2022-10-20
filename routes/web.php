<?php

use App\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});


Route::get('/', function () {
//    return new \App\Mail\VerifyEmail();
//    $email = new \Illuminate\Auth\Notifications\VerifyEmail();
//    return $email->toMail(\App\Models\User::find(16));
//    $notifiable = User::first();
//    URL::temporarySignedRoute(
//        'verification.verify',
//        Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
//        [
//            'id' => $notifiable->getKey(),
//            'hash' => sha1($notifiable->getEmailForVerification()),
//        ]
//    );
});

