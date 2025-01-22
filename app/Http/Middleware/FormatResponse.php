<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormatResponse
{
    /**
     * Removes unused data from paginated requests.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('_data')) {
            $data = json_decode($request->input('_data'), true);
            $request->request->remove('_data');
            $request->merge($data);
        }
        $response = $next($request);

        if ($response instanceof StreamedResponse) return $response;
        
        try {
            $data = $response->getData(true);

            if ($request->session()->has('impersonate'))
                $response->header('X-Impersonating', 'true');

            if (isset($data['links']) && isset($data['meta'])) {
                unset($data['links']);
                $data['meta'] = [
                    'total' => $data['meta']['total'],
                    'page' => $data['meta']['current_page'],
                    'pageSize' => $data['meta']['per_page'],
                ];
            }

            $response->setData($data);
            if ($response instanceof JsonResponse)
                $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);

            return $response;
        } catch (\Exception $exception) {
            return $response;
        }
    }
}
