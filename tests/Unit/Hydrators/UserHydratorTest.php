<?php

namespace Tests\Unit\Hydrators;

use App\Hydrators\UserHydrator;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class UserHydratorTest extends TestCase
{
    public function testModelToDto()
    {
        // Mock the Profile model
        $profile = $this->createMock(Profile::class);
        $profile->method('__get')->willReturnCallback(function ($property) {
            return match ($property) {
                'avatar' => 'test_avatar.png',
                'header' => 'test_header.png',
                'bio' => 'This is a bio.',
                'website' => 'https://example.com',
                default => null,
            };
        });

        // Mock the User model
        $user = $this->createMock(User::class);
        $user->method('__get')->willReturnCallback(function ($property) use ($profile) {
            return match ($property) {
                'id' => '12345',
                'name' => 'John Doe',
                'username' => 'johndoe',
                'profile' => $profile,
                'created_at' => new Carbon('2023-01-01'),
                default => null,
            };
        });

        // Create an instance of UserHydrator
        $urlGenerator = $this->createMock(\Illuminate\Routing\UrlGenerator::class);
        $urlGenerator->method('to')->willReturnCallback(function ($path) {
            return $path;
        });
        $hydrator = new UserHydrator($urlGenerator);

        // Call the method to test
        $dto = $hydrator->modelToDto($user);

        // Assertions
        $this->assertEquals('12345', $dto->id);
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('johndoe', $dto->username);
        $this->assertEquals('/files/test_avatar.png', $dto->avatar);
        $this->assertEquals('/files/test_header.png', $dto->header);
        $this->assertEquals('This is a bio.', $dto->bio);
        $this->assertEquals('https://example.com', $dto->website);
    }

    public function testModelToDtoWithoutProfile()
    {
        // Mock the User model without a profile
        $user = $this->createMock(User::class);
        $user->method('__get')->willReturnCallback(function ($property) {
            return match ($property) {
                'id' => '12345',
                'name' => 'John Doe',
                'username' => 'johndoe',
                'profile' => null,
                'created_at' => new Carbon('2023-01-01'),
                default => null,
            };
        });

        // Create an instance of UserHydrator
        $urlGenerator = $this->createMock(\Illuminate\Routing\UrlGenerator::class);
        $urlGenerator->method('to')->willReturnCallback(function ($path) {
            return $path;
        });
        $hydrator = new UserHydrator($urlGenerator);

        // Call the method to test
        $dto = $hydrator->modelToDto($user);

        // Assertions
        $this->assertEquals('12345', $dto->id);
        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals('johndoe', $dto->username);
        $this->assertNull($dto->avatar);
        $this->assertNull($dto->header);
        $this->assertNull($dto->bio);
        $this->assertNull($dto->website);
    }
}
