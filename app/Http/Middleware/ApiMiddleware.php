<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiMiddleware
{
    /**
     * If no accept header is set, force application/json
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->headers->has('Accept')) {
            $request->headers->set('Accept', 'application/json');
        }
        $response = $next($request);

        if ($response instanceof Response && ! $response->headers->has('Content-Type') || $response->headers->get('Content-Type') === 'text/html; charset=utf-8') {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
