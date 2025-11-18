<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ensi\LaravelPrometheus\Prometheus;

class PrometheusMetricsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        \Log::info('Prometheus middleware fired');
        $start = microtime(true);

        // Proceed with request
        $response = $next($request);

        $duration = microtime(true) - $start;

        // Update counter metric
        Prometheus::update('http_requests_count', 1, [
            $request->path(),
            $response->status()
        ]);

        // Update summary metric (latency)
        Prometheus::update('http_requests_duration_seconds', $duration);

        return $response;
    }
}
