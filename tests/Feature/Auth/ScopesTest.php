<?php

namespace Tests\Feature\Auth;

use App\Enums\Scopes\AdminWriteScope;
use App\Enums\Scopes\ReadScope;
use App\Enums\Scopes\Scope;
use App\Enums\Scopes\WriteScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_scope_grants_granular_scopes(): void
    {
        Passport::actingAs(User::factory()->create(), [Scope::Read->value]);

        $this->getJson('/api/auth/user')->assertOk();
        $this->getJson('/api/notifications/unread-count')->assertOk();
        $this->getJson('/api/locations/history')->assertOk();
        $this->postJson('/api/posts/text', ['body' => 'hi', 'visibility' => 'public'])->assertForbidden();
    }

    public function test_granular_scope_only_grants_itself(): void
    {
        Passport::actingAs(User::factory()->create(), [ReadScope::Notifications->value]);

        $this->getJson('/api/notifications/unread-count')->assertOk();
        $this->getJson('/api/auth/user')->assertForbidden();
        $this->getJson('/api/locations/history')->assertForbidden();
        $this->postJson('/api/notifications/read-all')->assertForbidden();
    }

    public function test_write_does_not_grant_read(): void
    {
        Passport::actingAs(User::factory()->create(), [Scope::Write->value]);

        $this->getJson('/api/auth/user')->assertForbidden();
        $this->postJson('/api/notifications/read-all')->assertSuccessful();
    }

    public function test_security_changes_need_security_scope(): void
    {
        Passport::actingAs(User::factory()->create(), [Scope::Read->value, WriteScope::Statuses->value]);

        $this->deleteJson('/api/account')->assertForbidden();
        $this->putJson('/api/auth/password', [])->assertForbidden();
    }

    public function test_admin_routes_need_admin_scope_even_for_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Passport::actingAs($admin, [Scope::Read->value, Scope::Write->value]);
        $this->postJson('/api/app/privacy-policy', [])->assertForbidden();

        Passport::actingAs($admin, [AdminWriteScope::Settings->value]);
        $this->postJson('/api/app/privacy-policy', [])->assertUnprocessable();
    }

    public function test_optional_auth_routes_reject_tokens_without_scope(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/profile/'.$user->username)->assertOk();

        Passport::actingAs($user, [ReadScope::Statuses->value]);
        $this->getJson('/api/profile/'.$user->username)->assertForbidden();
    }

    public function test_web_session_cookie_has_all_scopes(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $jwt = $this->get('/home')->getCookie(Passport::cookie())->getValue();
        $csrf = session()->token();
        $this->app['auth']->forgetGuards();

        $this->withCredentials()
            ->withCookie(Passport::cookie(), $jwt)
            ->withHeader('X-CSRF-TOKEN', $csrf)
            ->getJson('/api/locations/history')
            ->assertOk();
    }

    public function test_every_scope_has_a_translation(): void
    {
        foreach (Scope::all() as $id => $description) {
            $this->assertNotSame('auth.scopes.'.$id, $description, "Missing translation for $id");
        }
    }

    public function test_metadata_lists_all_scopes(): void
    {
        $this->getJson('/.well-known/oauth-authorization-server')
            ->assertJsonPath('scopes_supported', array_keys(Scope::all()));
    }
}
