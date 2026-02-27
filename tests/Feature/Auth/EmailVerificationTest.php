<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();
        Passport::actingAs($user);

        Event::fake();

        $response = $this->postJson('/api/auth/email/verify/'.$user->id.'/'.sha1($user->email));

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertOk();
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/auth/email/verify/'.$user->id.'/'.sha1('wrong-email'));

        $response->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
