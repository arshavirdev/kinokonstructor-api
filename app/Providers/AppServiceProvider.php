<?php

namespace App\Providers;

use App\Models\Request;
use App\Observers\RequestObserver;
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
    }
}
