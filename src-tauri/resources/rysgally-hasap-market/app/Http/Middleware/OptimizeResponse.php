<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptimizeResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Cache static assets for 30 days
        if ($this->isStaticAsset($request->path())) {
            $response->header('Cache-Control', 'public, max-age=2592000, immutable');
        }
        
        // Don't cache HTML pages but compress them
        if ($response->headers->get('Content-Type') && str_contains($response->headers->get('Content-Type'), 'text/html')) {
            $response->header('Cache-Control', 'public, max-age=3600');
            $response->header('ETag', md5($response->getContent()));
        }

        // Compress responses
        $response->header('Vary', 'Accept-Encoding');

        return $response;
    }

    private function isStaticAsset($path)
    {
        return str_contains($path, ['/css/', '/js/', '/assets/', '.ico', '.svg']);
    }
}
