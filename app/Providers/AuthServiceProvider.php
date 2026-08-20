<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\VerifyEmailController;
use App\Models\Profile;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\ProfilePolicy;
use App\Models\Comment;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Mail\VerifyEmail as CustomVerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Mail\ResetPassword as CustomResetPassword;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Comment::class => CommentPolicy::class,
        Profile::class => ProfilePolicy::class,
        // 'App\Models\Profile' => 'App\Policies\ProfilePolicy2'
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        ResetPassword::toMailUsing(fn (User $user, string $token) => (new CustomResetPassword($user, config('front.base_url') . '/auth/reset-password?token=' . $token)));

        VerifyEmail::toMailUsing(fn (User $user, string $verificationUrl) => (new CustomVerifyEmail($user, $verificationUrl)));
        VerifyEmail::createUrlUsing(function (User $notifiable) {
            $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 120))->timestamp;

            $url = VerifyEmailController::getSignedUrl($notifiable, $expires);
            Log::debug('AuthServiceProvider', [
                'signedUrl' => $url,
            ]);

            return $url;
        });
    }
}
