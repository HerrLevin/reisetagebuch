<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Imprint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ImprintControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_null_content_when_no_imprint_exists(): void
    {
        $response = $this->getJson(route('imprint.show'));

        $response->assertOk();
        $response->assertJson(['content' => null]);
    }

    public function test_show_returns_persisted_content(): void
    {
        Imprint::query()->create(['content' => 'Example imprint text']);

        $response = $this->getJson(route('imprint.show'));

        $response->assertOk();
        $response->assertJson(['content' => 'Example imprint text']);
    }

    public function test_update_forbidden_for_non_admin(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->patchJson(route('imprint.update'), [
            'content' => 'New imprint',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('imprints', 0);
    }

    public function test_update_unauthenticated_returns_unauthorized(): void
    {
        $response = $this->patchJson(route('imprint.update'), [
            'content' => 'New imprint',
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_persists_content_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        Passport::actingAs($admin);
        $response = $this->patchJson(route('imprint.update'), [
            'content' => 'Admin imprint text',
        ]);

        $response->assertOk();
        $response->assertJson(['content' => 'Admin imprint text']);

        $this->assertDatabaseCount('imprints', 1);
        $this->assertDatabaseHas('imprints', ['content' => 'Admin imprint text']);
    }

    public function test_show_returns_saved_content_after_update(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        Passport::actingAs($admin);
        $this->patchJson(route('imprint.update'), [
            'content' => 'Persisted imprint',
        ])->assertOk();

        $response = $this->getJson(route('imprint.show'));

        $response->assertOk();
        $response->assertJson(['content' => 'Persisted imprint']);
    }
}
