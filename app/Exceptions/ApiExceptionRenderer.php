<?php

namespace App\Exceptions;

use App\Dto\ErrorDto;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiExceptionRenderer
{
    public function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') || $this->hasBuiltInHandling($e)) {
            return null;
        }

        $isHttpException = $e instanceof HttpExceptionInterface;

        return response()->json(
            $this->toErrorDto($e, $isHttpException),
            $isHttpException ? $e->getStatusCode() : 500,
            $isHttpException ? $e->getHeaders() : []
        );
    }

    /**
     * ValidationException (422) and AuthenticationException (401) are already rendered
     * correctly by Laravel's default handler (per-field errors, "Unauthenticated." message)
     * before any registered render callback runs — leave those alone.
     */
    private function hasBuiltInHandling(Throwable $e): bool
    {
        return $e instanceof ValidationException || $e instanceof AuthenticationException;
    }

    private function toErrorDto(Throwable $e, bool $isHttpException): ErrorDto
    {
        if ($isHttpException) {
            $decoded = json_decode($e->getMessage(), true);

            $dto = is_array($decoded) && array_key_exists('message', $decoded)
                ? new ErrorDto(
                    message: (string) $decoded['message'],
                    errors: $decoded['errors'] ?? null,
                    code: $decoded['code'] ?? null,
                )
                : new ErrorDto(
                    message: $e->getMessage() !== ''
                        ? $e->getMessage()
                        : (Response::$statusTexts[$e->getStatusCode()] ?? 'Error'),
                );
        } else {
            // Unexpected/uncaught exceptions become a generic 500. Never leak the
            // real message here — it can contain sensitive internals (SQL, paths, ...).
            // The real message is only exposed via `debug` below, in debug mode.
            $dto = new ErrorDto(message: 'Server Error');
        }

        if (config('app.debug')) {
            $dto->debug = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())
                    ->map(fn (array $frame) => Arr::except($frame, ['args']))
                    ->all(),
            ];
        }

        return $dto;
    }
}
