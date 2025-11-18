<?php

namespace App\Providers;

use App\Models\Request;
use App\Observers\RequestObserver;
use Ensi\LaravelPrometheus\Prometheus;
use Illuminate\Http\Resources\Json\JsonResource;
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
    }
}
