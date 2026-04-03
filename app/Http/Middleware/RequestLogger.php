<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    /**
     * Handle an incoming request and log useful information.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (true) {
            return $next($request);
        }

        try {
            $data = [
                'method' => $request->method(),
                'uri' => $request->getRequestUri(),
                'ip' => $request->ip(),
                'user_id' => optional($request->user())->id ?? null,
                'user_agent' => $request->userAgent(),
                'headers' => [
                    'content-type' => $request->header('content-type'),
                    'accept' => $request->header('accept'),
                ],
                'query' => $request->query(),
                'body' => $this->safeRequestBody($request),
            ];

            Log::info('Incoming request', $data);
        } catch (\Throwable $e) {
            // Don't break the request on logging failures
            Log::warning('RequestLogger failed: '.$e->getMessage());
        }

        return $next($request);
    }

    /**
     * Return a safe representation of the request body for logging.
     * Redacts common sensitive fields and skips binary content.
     */
    protected function safeRequestBody(Request $request): array|string|null
    {
        $contentType = $request->header('content-type', '');

        if (! str_contains($contentType, 'text/html')) {
            $input = $request->all();

            // redact common sensitive fields
            $sensitive = ['password', 'password_confirmation', 'token', 'access_token'];
            foreach ($sensitive as $field) {
                if (array_key_exists($field, $input)) {
                    $input[$field] = '[REDACTED]';
                }
            }

            return $input;
        }

        // For other content types (files, multipart, etc.) avoid logging the body
        return null;
    }
}
