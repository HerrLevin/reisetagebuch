<?php

namespace Tests\Unit\Hydrators;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use App\Http\Resources\UserDto;
use App\Hydrators\PostHydrator;
use App\Hydrators\UserHydrator;
use App\Models\Location;
use App\Models\LocationPost;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PostHydratorTest extends TestCase
{
    private function userMock(): UserDto
    {
        $userDto = new UserDto;
        $userDto->id = '1';
        $userDto->name = 'John Doe';
        $userDto->username = 'johnDoe';
        $userDto->createdAt = Carbon::now()->toIso8601String();

        return $userDto;
    }

    private function postMock(?TransportPost $transportPost = null, ?LocationPost $locationPost = null): Post
    {
        $post = $this->getMockBuilder(Post::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();

        $post->method('__get')->willReturnCallback(function ($property) use ($transportPost, $locationPost) {
            return match ($property) {
                'id' => 'asdf',
                'body' => 'This is a test post',
                'created_at' => Carbon::parse('2023-01-01 00:00:00'),
                'updated_at' => Carbon::parse('2023-01-01 00:00:01'),
                'published_at' => Carbon::parse('2023-01-01 00:00:02'),
                'transportPost' => $transportPost,
                'locationPost' => $locationPost,
                'visibility' => Visibility::PUBLIC,
                'user' => new User,
                'metaInfos' => new Collection([
                    $this->createMetaInfoMock(MetaInfoKey::TRAVEL_REASON, TravelReason::LEISURE->value),
                ]),
                default => null,
            };
        });

        return $post;
    }

    private function createMetaInfoMock(MetaInfoKey $key, string $value, ?int $order = null)
    {
        $mock = $this->createMock(\App\Models\PostMetaInfo::class);
        $callback = function ($property) use ($key, $value, $order) {
            return match ($property) {
                'key' => $key,
                'value' => $value,
                'order' => $order,
                default => null,
            };
        };

        $mock->method('__get')->willReturnCallback($callback);
        $mock->method('offsetGet')->willReturnCallback($callback);
        $mock->method('offsetExists')->willReturn(true);

        return $mock;
    }

    /**
     * @throws Exception
     */
    public function test_model_to_dto()
    {
        $userHydrator = $this->createMock(UserHydrator::class);
        $userHydrator->method('modelToDto')
            ->willReturn($this->userMock());
        $post = $this->postMock();

        $postHydrator = new PostHydrator($userHydrator);
        $dto = $postHydrator->modelToDto($post);
        $this->assertNotInstanceOf(LocationPost::class, $dto);
        $this->assertNotInstanceOf(TransportPost::class, $dto);
        $this->assertEquals('asdf', $dto->id);
        $this->assertEquals('This is a test post', $dto->body);
        $this->assertEquals('2023-01-01T00:00:00+00:00', $dto->created_at);
        $this->assertEquals('2023-01-01T00:00:01+00:00', $dto->updated_at);
        $this->assertEquals('2023-01-01T00:00:02+00:00', $dto->published_at);
        $this->assertEquals($userHydrator->modelToDto($post->user), $dto->user);
        $this->assertEquals([MetaInfoKey::TRAVEL_REASON->value => TravelReason::LEISURE->value], $dto->metaInfos);
    }

    /**
     * @throws Exception
     */
    public function test_model_to_dto_with_vehicle_ids()
    {
        $userHydrator = $this->createMock(UserHydrator::class);
        $userHydrator->method('modelToDto')
            ->willReturn($this->userMock());

        $vehicle1 = $this->createMetaInfoMock(MetaInfoKey::VEHICLE_ID, 'v1', 0);
        $vehicle2 = $this->createMetaInfoMock(MetaInfoKey::VEHICLE_ID, 'v2', 1);

        $post = $this->postMock();
        // Override metaInfos for this test
        $post = $this->getMockBuilder(Post::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();

        $post->method('__get')->willReturnCallback(function ($property) use ($vehicle1, $vehicle2) {
            return match ($property) {
                'id' => 'asdf',
                'body' => 'This is a test post',
                'created_at' => Carbon::parse('2023-01-01 00:00:00'),
                'updated_at' => Carbon::parse('2023-01-01 00:00:01'),
                'published_at' => Carbon::parse('2023-01-01 00:00:02'),
                'transportPost' => null,
                'locationPost' => null,
                'visibility' => Visibility::PUBLIC,
                'user' => new User,
                'metaInfos' => new Collection([$vehicle2, $vehicle1]), // Wrong order to test sorting
                default => null,
            };
        });

        $postHydrator = new PostHydrator($userHydrator);
        $dto = $postHydrator->modelToDto($post);

        $this->assertEquals(['rtb:vehicle_id' => ['v1', 'v2']], $dto->metaInfos);
    }

    /**
     * @throws Exception
     */
    public function test_model_to_dto_with_location_post()
    {
        $location = $this->getLocationMock();

        $locationPost = $this->createMock(LocationPost::class);
        $locationPost->method('__get')->willReturnCallback(function ($property) use ($location) {
            return match ($property) {
                'location' => $location,
                default => null,
            };
        });

        $userHydrator = $this->createMock(UserHydrator::class);
        $userHydrator->method('modelToDto')
            ->willReturn($this->userMock());
        $post = $this->postMock(null, $locationPost);

        $postHydrator = new PostHydrator($userHydrator);
        $dto = $postHydrator->modelToDto($post);
        $this->assertInstanceOf(\App\Http\Resources\PostTypes\LocationPost::class, $dto);
        $this->assertEquals('asdf', $dto->id);
        $this->assertEquals('This is a test post', $dto->body);
        $this->assertEquals('2023-01-01T00:00:00+00:00', $dto->created_at);
        $this->assertEquals('2023-01-01T00:00:01+00:00', $dto->updated_at);
        $this->assertEquals($userHydrator->modelToDto($post->user), $dto->user);
    }

    /**
     * @throws Exception
     */
    public function test_model_to_dto_with_transport_post()
    {
        $transportTrip = $this->getMockBuilder(TransportTrip::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();

        $transportTrip->method('__get')->willReturnCallback(function ($property) {
            return match ($property) {
                'id' => 'trip-id',
                'provider' => 'Test Provider',
                'mode' => 'bus',
                'line_name' => '123',
                default => null,
            };
        });

        $transportPost = $this->getMockBuilder(TransportPost::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();

        $transportPost->method('__get')->willReturnCallback(function ($property) use ($transportTrip) {
            return match ($property) {
                'originStop' => $this->getTransportTripStop('2023-01-01 00:00:00'),
                'destinationStop' => $this->getTransportTripStop(),
                'transportTrip' => $transportTrip,
                default => null,
            };
        });

        $userHydrator = $this->createMock(UserHydrator::class);
        $userHydrator->method('modelToDto')
            ->willReturn($this->userMock());
        $post = $this->postMock($transportPost);

        $postHydrator = new PostHydrator($userHydrator);
        $dto = $postHydrator->modelToDto($post);
        $this->assertInstanceOf(\App\Http\Resources\PostTypes\TransportPost::class, $dto);
        $this->assertEquals('asdf', $dto->id);
        $this->assertEquals('This is a test post', $dto->body);
        $this->assertEquals('2023-01-01T00:00:00+00:00', $dto->created_at);
        $this->assertEquals('2023-01-01T00:00:01+00:00', $dto->updated_at);
        $this->assertEquals($userHydrator->modelToDto($post->user), $dto->user);
        $this->assertEquals('2023-01-01T00:00:00+00:00', $dto->originStop->arrivalTime);
        $this->assertEquals('2023-01-01T00:00:01+00:00', $dto->destinationStop->arrivalTime);
        $this->assertEquals('bus', $dto->trip->mode);
        $this->assertEquals('123', $dto->trip->lineName);
        $this->assertEquals('location-id', $dto->originStop->location->id);
        $this->assertEquals('Test Location', $dto->originStop->location->name);
        $this->assertEquals(1.1, $dto->originStop->location->latitude);
        $this->assertEquals(2.1, $dto->originStop->location->longitude);

    }

    private function getTransportTripStop(
        string $departureTime = '2023-01-01 00:00:01'
    ): TransportTripStop {
        $arrivalTime = '2023-01-01 00:00:00';
        $stop = $this->createMock(TransportTripStop::class);
        $stop->method('__get')->willReturnCallback(function ($property) use ($departureTime, $arrivalTime) {
            return match ($property) {
                'id' => 'stop-id',
                'location' => $this->getLocationMock(),
                'arrival_time' => Carbon::parse($departureTime),
                'departure_time' => Carbon::parse($arrivalTime),
                'arrival_delay' => 5,
                'departure_delay' => 10,
                default => null,
            };
        });

        return $stop;
    }

    /**
     * @throws Exception
     */
    private function getLocationMock(): Location
    {
        $point = $this->createMock(Point::class);
        $point->method('getLatitude')->willReturn(1.1);
        $point->method('getLongitude')->willReturn(2.1);

        $location = $this->createMock(Location::class);
        $location->method('__get')->willReturnCallback(function ($property) use ($point) {
            return match ($property) {
                'id' => 'location-id',
                'name' => 'Test Location',
                'location' => $point,
                'distance' => 10,
                'tags' => new Collection,
                'identifiers' => new Collection,
                default => null,
            };
        });

        return $location;
    }
}
