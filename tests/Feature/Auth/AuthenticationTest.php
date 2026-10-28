<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_login_form_is_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="password"', false);
    }

    public function test_users_can_log_in(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/home');

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_users_can_not_log_in_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_authenticated_users_visiting_login_are_redirected_home(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->get('/login')
            ->assertRedirect('/home');
    }

    public function test_spa_page_load_sets_api_token_cookie_usable_for_the_api(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get('/home')->assertOk()->assertCookie(Passport::cookie());
        $jwt = $response->getCookie(Passport::cookie())->getValue();
        $csrf = session()->token();

        $this->app['auth']->forgetGuards();
        $this->withCredentials()
            ->withCookie(Passport::cookie(), $jwt)
            ->withHeader('X-CSRF-TOKEN', $csrf)
            ->getJson('/api/auth/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_api_token_cookie_requires_csrf_header(): void
    {
        $this->actingAs(User::factory()->create(), 'web');
        $jwt = $this->get('/home')->getCookie(Passport::cookie())->getValue();

        $this->app['auth']->forgetGuards();
        $this->withCredentials()
            ->withCookie(Passport::cookie(), $jwt)
            ->getJson('/api/auth/user')
            ->assertUnauthorized();
    }

    public function test_web_logout_ends_session_and_clears_api_token_cookie(): void
    {
        $this->actingAs(User::factory()->create(), 'web');

        $this->post('/logout')
            ->assertNoContent()
            ->assertCookieExpired(Passport::cookie());

        $this->assertGuest('web');
    }

    public function test_api_logout_revokes_bearer_token(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $this->postJson('/api/auth/logout')->assertOk();
    }
}
