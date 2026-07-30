<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ApiExceptionRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bare_abort_403_is_rendered_as_error_dto(): void
    {
        config(['app.debug' => false]);
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->patchJson(route('imprint.update'), ['content' => 'New imprint']);

        $response->assertForbidden();
        $response->assertExactJson([
            'message' => 'Forbidden',
            'errors' => null,
            'code' => null,
            'debug' => null,
        ]);
    }

    public function test_bare_abort_404_is_rendered_as_error_dto(): void
    {
        config(['app.debug' => false]);
        $response = $this->getJson(route('privacy-policy.show'));

        $response->assertNotFound();
        $response->assertExactJson([
            'message' => 'Not Found',
            'errors' => null,
            'code' => null,
            'debug' => null,
        ]);
    }

    public function test_debug_mode_includes_stacktrace_for_api_errors(): void
    {
        config(['app.debug' => true]);

        $response = $this->getJson(route('privacy-policy.show'));

        $response->assertNotFound();
        $response->assertJsonStructure([
            'message',
            'errors',
            'code',
            'debug' => ['exception', 'message', 'file', 'line', 'trace'],
        ]);
    }
}
