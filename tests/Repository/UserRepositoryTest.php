<?php

namespace Repository;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testGetUserByUsername()
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'bio' => $user->bio,
            'website' => $user->website,
            'avatar' => $user->avatar,
            'header' => $user->header,
        ]);

        $repository = new UserRepository();
        $result = $repository->getUserByUsername($user->username);

        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->name, $result->name);
        $this->assertEquals($user->username, $result->username);

        $this->expectException(ModelNotFoundException::class);
        $result2 = $repository->getUserByUsername('nonexistentuser');
        $this->assertNull($result2);
    }

    public function testUpdateUser()
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'bio' => $user->bio,
            'website' => $user->website,
            'avatar' => $user->avatar,
            'header' => $user->header,
        ]);

        $repository = new UserRepository();
        $updatedUser = $repository->updateUser($user, 'New Name', 'New Bio', 'New Website', 'new_avatar.png', 'new_header.png');

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('New Bio', $updatedUser->bio);
        $this->assertEquals('New Website', $updatedUser->website);
        $this->assertEquals(url('/files/new_avatar.png'), $updatedUser->avatar);
        $this->assertEquals(url('/files/new_header.png'), $updatedUser->header);

        $this->assertEquals($user->id, $updatedUser->id);

        $updatedUser = $repository->updateUser($user, 'Updated Name', null, null, null, null);
        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertNull($updatedUser->bio);
        $this->assertNull($updatedUser->website);
        $this->assertNull($updatedUser->avatar);
        $this->assertNull($updatedUser->header);
    }

    public function testCreateProfileIfNotExists()
    {
        $user = User::factory()->create();

        $repository = new UserRepository();
        $updatedUser = $repository->updateUser($user, 'New Name', 'New Bio', null, null, null);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('New Bio', $updatedUser->bio);
        $this->assertEquals($user->id, $updatedUser->id);
    }
}
