<?php

namespace Tests\Feature\Hydrators;

use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\ActivityPub\ActivityPubPostHydrator;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityPubPostHydratorTest extends TestCase
{
    use RefreshDatabase;

    private function hydrator(): ActivityPubPostHydrator
    {
        return new ActivityPubPostHydrator;
    }

    private function makePost(?array $extensionData): ActivityPubPost
    {
        $actor = ActivityPubActor::factory()->create();

        return ActivityPubPost::create([
            'id' => Str::uuid(),
            'activity_pub_actor_id' => $actor->id,
            'activity_id' => 'https://remote.example/notes/'.Str::uuid(),
            'content' => 'Hello world',
            'extension_data' => $extensionData,
            'published_at' => now(),
        ]);
    }

    public function test_returns_base_post_when_no_extension_data(): void
    {
        $post = $this->makePost(null);

        $dto = $this->hydrator()->modelToDto($post);

        $this->assertInstanceOf(BasePost::class, $dto);
        $this->assertNotInstanceOf(LocationPost::class, $dto);
        $this->assertNotInstanceOf(TransportPost::class, $dto);
    }

    public function test_returns_location_post_when_extension_data_is_a_location(): void
    {
        $post = $this->makePost([
            'rtbVersion' => 1,
            'postType' => 'location',
            'location' => [
                'id' => 'loc-1',
                'name' => 'Berlin Hbf',
                'latitude' => 52.52,
                'longitude' => 13.405,
                'timezone' => 'Europe/Berlin',
                'emoji' => '🚉',
                'tags' => [['key' => 'addr:city', 'value' => 'Berlin']],
                'identifiers' => [],
                'travelReason' => 'leisure',
                'visitedAt' => '2026-01-01T10:00:00+00:00',
            ],
        ]);

        $dto = $this->hydrator()->modelToDto($post);

        $this->assertInstanceOf(LocationPost::class, $dto);
        $this->assertSame('Berlin Hbf', $dto->location->name);
        $this->assertStringContainsString('Berlin Hbf', $dto->getHtmlBody());
    }

    public function test_returns_transport_post_when_extension_data_is_transport(): void
    {
        $stop = [
            'id' => 'stop-1',
            'name' => 'Berlin Hbf',
            'location' => [
                'id' => 'loc-1',
                'name' => 'Berlin Hbf',
                'latitude' => 52.52,
                'longitude' => 13.405,
                'timezone' => 'Europe/Berlin',
                'emoji' => '🚉',
                'tags' => [],
                'identifiers' => [],
            ],
            'arrivalTime' => null,
            'departureTime' => '2026-01-01T10:00:00+00:00',
            'arrivalDelay' => null,
            'departureDelay' => 0,
        ];

        $post = $this->makePost([
            'rtbVersion' => 1,
            'postType' => 'transport',
            'transport' => [
                'originStop' => $stop,
                'destinationStop' => $stop,
                'trip' => [
                    'id' => 'trip-1',
                    'foreignId' => null,
                    'mode' => 'RAIL',
                    'lineName' => 'RE1',
                    'routeLongName' => null,
                    'tripShortName' => null,
                    'displayName' => 'RE1',
                    'routeColor' => null,
                    'routeTextColor' => null,
                ],
                'manualDepartureTime' => null,
                'manualArrivalTime' => null,
                'travelReason' => 'commute',
                'distance' => 12345,
                'duration' => 600,
                'userGeometry' => null,
            ],
        ]);

        $dto = $this->hydrator()->modelToDto($post);

        $this->assertInstanceOf(TransportPost::class, $dto);
        $this->assertSame('RE1', $dto->trip->displayName);
        $this->assertStringContainsString('RE1', $dto->getHtmlBody());
    }

    public function test_falls_back_to_base_post_when_extension_data_is_corrupted(): void
    {
        $post = $this->makePost([
            'rtbVersion' => 1,
            'postType' => 'location',
            // 'location' key missing entirely — LocationDto::fromRemote() will be
            // called with [] and still succeed, so make it a hard type error instead.
            'location' => 'not-an-array',
        ]);

        $dto = $this->hydrator()->modelToDto($post);

        $this->assertInstanceOf(BasePost::class, $dto);
        $this->assertNotInstanceOf(LocationPost::class, $dto);
    }
}
