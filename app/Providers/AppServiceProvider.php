<?php

namespace App\Providers;

use App\Models\Request;
use App\Observers\RequestObserver;
use Ensi\LaravelPrometheus\Prometheus;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        JsonResource::withoutWrapping();
        if (!app()->environment('local')) {
            \URL::forceScheme('https');
        }

        Request::observe(RequestObserver::class);

        Prometheus::counter('http_requests_count')->labels(['endpoint', 'code']);
        Prometheus::summary('http_requests_duration_seconds', 60, [0.5, 0.95, 0.99]);

        RateLimiter::for('api', function (HttpRequest $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('verify-email', function (HttpRequest $request) {
            return $request->user()
                ? Limit::perMinute(1)->by($request->user()->id)
                : Limit::perMinute(1)->by($request->ip());
        });
    }
}
