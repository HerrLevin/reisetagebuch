<?php

namespace Tests\Repository;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_user_by_username()
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'bio' => $user->bio,
            'website' => $user->website,
            'avatar' => $user->avatar,
            'header' => $user->header,
        ]);

        $repository = new UserRepository;
        $result = $repository->getUserByUsername($user->username);

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->name, $result->name);
        $this->assertEquals($user->username, $result->username);

        $this->expectException(ModelNotFoundException::class);
        $result2 = $repository->getUserByUsername('nonexistentuser');
        $this->assertNull($result2);
    }

    public function test_update_user()
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'bio' => $user->bio,
            'website' => $user->website,
            'avatar' => $user->avatar,
            'header' => $user->header,
        ]);

        $repository = new UserRepository;
        $updatedUser = $repository->updateUser($user, 'New Name', 'New Bio', 'New Website');

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('New Bio', $updatedUser->bio);
        $this->assertEquals('New Website', $updatedUser->website);

        $updatedUser = $repository->updateAvatar($user, 'new_avatar.png', 'image/png');

        $this->assertEquals(url('/files/new_avatar.png'), $updatedUser->avatar);

        $updatedUser = $repository->updateHeader($user, 'new_header.png', 'image/png');
        $this->assertEquals(url('/files/new_header.png'), $updatedUser->header);

        $this->assertEquals($user->id, $updatedUser->id);

        $updatedUser = $repository->updateUser($user, 'Updated Name', null, null, null, null);
        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertNull($updatedUser->bio);
        $this->assertNull($updatedUser->website);
        $this->assertNotNull($updatedUser->avatar);
        $this->assertNotNull($updatedUser->avatarMimeType);
        $this->assertNotNull($updatedUser->header);
        $this->assertNotNull($updatedUser->headerMimeType);

        $repository->updateAvatar($user, null, null);
        $updatedUser = $repository->updateHeader($user, null, null);

        $this->assertNull($updatedUser->avatar);
        $this->assertNull($updatedUser->avatarMimeType);
        $this->assertNull($updatedUser->header);
        $this->assertNull($updatedUser->headerMimeType);
    }

    public function test_search_users_matches_username(): void
    {
        $user = User::factory()->create(['username' => 'traveldiaryfan']);
        User::factory()->create(['username' => 'someoneelse']);

        $repository = new UserRepository;
        $results = $repository->searchUsers('traveldiary');

        $this->assertCount(1, $results);
        $this->assertSame($user->id, $results[0]->id);
    }

    public function test_search_users_matches_name(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);
        User::factory()->create(['name' => 'Someone Else']);

        $repository = new UserRepository;
        $results = $repository->searchUsers('Lovelace');

        $this->assertCount(1, $results);
        $this->assertSame($user->id, $results[0]->id);
    }

    public function test_search_users_is_case_insensitive(): void
    {
        $user = User::factory()->create(['username' => 'CaseSensitiveUser']);

        $repository = new UserRepository;
        $results = $repository->searchUsers('casesensitive');

        $this->assertCount(1, $results);
        $this->assertSame($user->id, $results[0]->id);
    }

    public function test_search_users_respects_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            User::factory()->create(['username' => 'searchlimit'.$i]);
        }

        $repository = new UserRepository;
        $results = $repository->searchUsers('searchlimit', limit: 2);

        $this->assertCount(2, $results);
    }

    public function test_search_users_returns_empty_array_for_no_matches(): void
    {
        User::factory()->create(['username' => 'someone']);

        $repository = new UserRepository;
        $results = $repository->searchUsers('nonexistentquery');

        $this->assertSame([], $results);
    }

    public function test_create_profile_if_not_exists()
    {
        $user = User::factory()->create();

        $repository = new UserRepository;
        $updatedUser = $repository->updateUser($user, 'New Name', 'New Bio', null, null, null);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('New Bio', $updatedUser->bio);
        $this->assertEquals($user->id, $updatedUser->id);
    }
}
