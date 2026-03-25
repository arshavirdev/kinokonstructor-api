<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Profile;
use App\Models\User;
use Hash;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;
use Str;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                $token = $request->user()->createToken($request->device_name ?? 'web')->plainTextToken;
                return response()->json(['token' => $token]);
            }
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::authenticateUsing(function (Request $request) {
            $user = null;
            if (is_numeric($request->login) && strlen($request->login) === 8) {
                $profile = Profile::where('member_id', $request->login)->first();
                if ($profile) {
                    $user = $profile->user;
                    if (!$user) abort(404, 'Member is not registered');
                }
            } else {
                $column = Str::contains($request->login, '@') ? 'email' : 'username';
                $user = User::where($column, $request->login)->first();
            }

            if ($user && Hash::check($request->password, $user->password))
                return $user;
        });
        RateLimiter::for('login', function (Request $request) {
            $email = (string)$request->email;
            return Limit::perMinute(100)->by($email . $request->ip());
        });
    }
}
