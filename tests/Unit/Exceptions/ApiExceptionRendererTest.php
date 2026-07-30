<?php

namespace Tests\Unit\Exceptions;

use App\Dto\ErrorDto;
use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ApiExceptionRendererTest extends TestCase
{
    private function apiRequest(): Request
    {
        return Request::create('/api/app/imprint', 'GET');
    }

    public function test_ignores_requests_outside_the_api_prefix(): void
    {
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new NotFoundHttpException, Request::create('/settings', 'GET'));

        $this->assertNull($response);
    }

    public function test_bare_http_exception_falls_back_to_status_text(): void
    {
        config(['app.debug' => false]);
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new HttpException(403), $this->apiRequest());

        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Forbidden',
            'errors' => null,
            'code' => null,
            'debug' => null,
        ], json_decode($response->getContent(), true));
    }

    public function test_http_exception_with_plain_message_is_used_as_is(): void
    {
        config(['app.debug' => false]);
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new HttpException(404, 'Location not found'), $this->apiRequest());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Location not found',
            'errors' => null,
            'code' => null,
            'debug' => null,
        ], json_decode($response->getContent(), true));
    }

    public function test_error_dto_passed_as_message_is_unwrapped_not_double_encoded(): void
    {
        config(['app.debug' => false]);
        $renderer = new ApiExceptionRenderer;

        $exception = new HttpException(400, (string) new ErrorDto('Invalid stops provided', code: 'invalid_stops'));

        $response = $renderer->render($exception, $this->apiRequest());

        $this->assertSame([
            'message' => 'Invalid stops provided',
            'errors' => null,
            'code' => 'invalid_stops',
            'debug' => null,
        ], json_decode($response->getContent(), true));
    }

    public function test_debug_info_is_included_when_debug_mode_is_enabled(): void
    {
        config(['app.debug' => true]);
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new HttpException(500, 'Something broke'), $this->apiRequest());

        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('debug', $body);
        $this->assertSame(HttpException::class, $body['debug']['exception']);
        $this->assertSame('Something broke', $body['debug']['message']);
        $this->assertArrayHasKey('file', $body['debug']);
        $this->assertArrayHasKey('line', $body['debug']);
        $this->assertArrayHasKey('trace', $body['debug']);
    }

    public function test_debug_info_is_omitted_when_debug_mode_is_disabled(): void
    {
        config(['app.debug' => false]);
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new HttpException(500, 'Something broke'), $this->apiRequest());

        $body = json_decode($response->getContent(), true);
        $this->assertNull($body['debug']);
    }

    public function test_generic_uncaught_exception_is_rendered_as_a_500(): void
    {
        config(['app.debug' => false]);
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new RuntimeException('SQLSTATE[28000]: user=root password=hunter2'), $this->apiRequest());

        $this->assertNotNull($response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Server Error',
            'errors' => null,
            'code' => null,
            'debug' => null,
        ], json_decode($response->getContent(), true));
    }

    public function test_generic_uncaught_exception_message_never_leaks_even_in_debug_mode(): void
    {
        config(['app.debug' => true]);
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new RuntimeException('SQLSTATE[28000]: user=root password=hunter2'), $this->apiRequest());

        $body = json_decode($response->getContent(), true);
        $this->assertSame('Server Error', $body['message']);
        $this->assertSame(RuntimeException::class, $body['debug']['exception']);
        $this->assertSame('SQLSTATE[28000]: user=root password=hunter2', $body['debug']['message']);
    }

    public function test_ignores_validation_exceptions(): void
    {
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(
            ValidationException::withMessages(['content' => 'The content field is required.']),
            $this->apiRequest()
        );

        $this->assertNull($response);
    }

    public function test_ignores_authentication_exceptions(): void
    {
        $renderer = new ApiExceptionRenderer;

        $response = $renderer->render(new AuthenticationException, $this->apiRequest());

        $this->assertNull($response);
    }
}
