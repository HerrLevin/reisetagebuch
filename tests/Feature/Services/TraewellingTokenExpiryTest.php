<?php

namespace Tests\Feature\Services;

use App\Exceptions\TraewellingTokenExpired;
use App\Http\Controllers\Backend\AccountBackend;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Socialite\TraewellingProvider;
use App\Services\TraewellingRequestService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
use Mockery;
use Tests\TestCase;

class TraewellingTokenExpiryTest extends TestCase
{
    use RefreshDatabase;

    private function expiredAccount(): SocialAccount
    {
        $account = SocialAccount::factory()->create(['token_expires_at' => now()->subDay()]);

        $driver = Mockery::mock(TraewellingProvider::class);
        $driver->shouldReceive('refreshToken')->andThrow(new ClientException(
            'invalid_grant',
            new Request('POST', 'oauth/token'),
            new Response(400, [], '{"error":"invalid_grant"}')
        ));
        Socialite::shouldReceive('driver')->with('traewelling')->andReturn($driver);

        return $account;
    }

    public function test_rejected_refresh_token_removes_account_and_throws(): void
    {
        Log::spy();
        $account = $this->expiredAccount();

        $this->expectException(TraewellingTokenExpired::class);
        try {
            (new TraewellingRequestService)->getAccessToken($account->user_id);
        } finally {
            $this->assertModelMissing($account);
            Log::shouldHaveReceived('warning')->once()->with(
                'Traewelling refresh token rejected, removing linked account',
                ['userId' => $account->user_id, 'exception' => 'invalid_grant']
            );
        }
    }

    public function test_command_refreshes_only_stale_tokens(): void
    {
        $stale = SocialAccount::factory()->create(['access_token' => 'old', 'refresh_token' => 'old-refresh']);
        $stale->forceFill(['updated_at' => now()->subDays(8)])->saveQuietly();
        $fresh = SocialAccount::factory()->create(['access_token' => 'fresh']);

        $driver = Mockery::mock(TraewellingProvider::class);
        $driver->shouldReceive('refreshToken')->once()->with('old-refresh')->andReturn(new Token('new', 'new-refresh', 3600, []));
        Socialite::shouldReceive('driver')->with('traewelling')->andReturn($driver);

        $this->artisan('app:refresh-traewelling-tokens')
            ->expectsOutput('Refreshed 1 Träwelling accounts, 0 failed.')
            ->assertSuccessful();

        $this->assertSame('new', $stale->fresh()->access_token);
        $this->assertSame('new-refresh', $stale->fresh()->refresh_token);
        $this->assertSame('fresh', $fresh->fresh()->access_token);
    }

    public function test_disconnect_removes_account_even_if_refresh_token_expired(): void
    {
        $account = $this->expiredAccount();

        $this->assertTrue(app(AccountBackend::class)->disconnectTraewelling(User::find($account->user_id)));
        $this->assertModelMissing($account);
    }
}
