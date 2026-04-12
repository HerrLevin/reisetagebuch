<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Imprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CypressControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_returns_forbidden_when_cypress_disabled(): void
    {
        config(['app.testing.cypress' => false]);

        $response = $this->postJson('/api/cypress/seed', [], [
            'X-Cypress-Token' => config('app.testing.cypress_token'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_seed_returns_forbidden_with_invalid_token(): void
    {
        $response = $this->postJson('/api/cypress/seed', [], [
            'X-Cypress-Token' => 'invalid-token',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_seed_returns_forbidden_without_token(): void
    {
        $response = $this->postJson('/api/cypress/seed');

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_seed_runs_migration_with_seeder(): void
    {
        $response = $this->postJson('/api/cypress/seed', [], [
            'X-Cypress-Token' => config('app.testing.cypress_token'),
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Database seeded successfully.']);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_reset_returns_forbidden_when_cypress_disabled(): void
    {
        Imprint::create([
            'content' => 'Test Imprint',
        ]);
        Imprint::create([
            'content' => 'Test Imprint 2',
        ]);
        config(['app.testing.cypress' => false]);

        $response = $this->postJson('/api/cypress/reset', [], [
            'X-Cypress-Token' => config('app.testing.cypress_token'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('imprints', 2);
    }

    public function test_reset_returns_forbidden_with_invalid_token(): void
    {
        Imprint::create([
            'content' => 'Test Imprint',
        ]);
        Imprint::create([
            'content' => 'Test Imprint 2',
        ]);
        $response = $this->postJson('/api/cypress/reset', [], [
            'X-Cypress-Token' => 'invalid-token',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('imprints', 2);
    }

    public function test_reset_returns_forbidden_without_token(): void
    {
        Imprint::create([
            'content' => 'Test Imprint',
        ]);
        Imprint::create([
            'content' => 'Test Imprint 2',
        ]);
        $response = $this->postJson('/api/cypress/reset');

        $response->assertForbidden();
        $this->assertDatabaseCount('imprints', 2);
    }

    public function test_reset_runs_migration_without_seeder(): void
    {
        Imprint::create([
            'content' => 'Test Imprint',
        ]);
        Imprint::create([
            'content' => 'Test Imprint 2',
        ]);
        $this->assertDatabaseCount('imprints', 2);
        $response = $this->postJson('/api/cypress/reset', [], [
            'X-Cypress-Token' => config('app.testing.cypress_token'),
        ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Database reset successfully.']);

        $this->assertDatabaseCount('imprints', 0);
    }
}
