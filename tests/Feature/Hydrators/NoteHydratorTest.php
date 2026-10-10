<?php

namespace Tests\Feature\Hydrators;

use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\ActivityPub\ActivityPubPostHydrator;
use App\Hydrators\ActivityPub\NoteHydrator;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubPost;
use App\Models\Location;
use App\Models\LocationTag;
use App\Models\Post;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use App\Repositories\PostRepository;
use App\Services\ActivityPubExtensionParser;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NoteHydratorTest extends TestCase
{
    use RefreshDatabase;

    private function hydrator(): NoteHydrator
    {
        return new NoteHydrator;
    }

    public function test_plain_text_post_has_no_extension_key_at_all(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC->value]);
        $repo = new PostRepository;
        $dto = $repo->getById($post->id, null);

        $note = $this->hydrator()->hydrate($dto, 'https://example.com/actor', 'https://example.com/actor/followers');
        $array = $note->toArray();

        $this->assertArrayNotHasKey('rtbExtension', $array);
    }

    public function test_location_post_includes_extension_with_expected_fields(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create(['name' => 'Berlin Hbf']);
        LocationTag::create(['location_id' => $location->id, 'key' => 'addr:city', 'value' => 'Berlin']);

        $visitedAt = Carbon::parse('2026-01-01 10:00:00');
        $repo = new PostRepository;
        $created = $repo->storeLocation($user, $location, Visibility::PUBLIC, 'Nice place', [], TravelReason::LEISURE, $visitedAt);
        $dto = $repo->getById($created->id, null);

        $this->assertInstanceOf(LocationPost::class, $dto);

        $note = $this->hydrator()->hydrate($dto, 'https://example.com/actor', 'https://example.com/actor/followers');
        $array = $note->toArray();

        $this->assertArrayHasKey('rtbExtension', $array);
        $this->assertSame(1, $array['rtbExtension']['rtbVersion']);
        $this->assertSame('location', $array['rtbExtension']['postType']);
        $this->assertSame('Berlin Hbf', $array['rtbExtension']['location']['name']);
        $this->assertSame('leisure', $array['rtbExtension']['location']['travelReason']);
        $this->assertNotEmpty($array['rtbExtension']['location']['tags']);
    }

    public function test_transport_post_includes_extension_with_expected_fields(): void
    {
        $user = User::factory()->create();
        $trip = TransportTrip::factory()->create(['line_name' => 'RE1']);
        $originStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 0,
            'departure_time' => Carbon::parse('2026-01-01 10:00:00'),
            'departure_delay' => 0,
        ]);
        $destinationStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 1,
        ]);

        $repo = new PostRepository;
        $created = $repo->storeTransport($user, $trip, $originStop, $destinationStop, Visibility::PUBLIC);
        $dto = $repo->getById($created->id, null);

        $this->assertInstanceOf(TransportPost::class, $dto);

        $note = $this->hydrator()->hydrate($dto, 'https://example.com/actor', 'https://example.com/actor/followers');
        $array = $note->toArray();

        $this->assertArrayHasKey('rtbExtension', $array);
        $this->assertSame('transport', $array['rtbExtension']['postType']);
        $this->assertSame('RE1', $array['rtbExtension']['transport']['trip']['lineName']);
        $this->assertArrayHasKey('originStop', $array['rtbExtension']['transport']);
        $this->assertArrayHasKey('destinationStop', $array['rtbExtension']['transport']);
    }

    public function test_context_only_includes_rtb_term_when_extension_is_set(): void
    {
        $user = User::factory()->create();
        $textPost = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC->value]);

        $location = Location::factory()->create();
        $repo = new PostRepository;
        $locationPost = $repo->storeLocation($user, $location, Visibility::PUBLIC);

        $plainDto = $repo->getById($textPost->id, null);
        $locationDto = $repo->getById($locationPost->id, null);

        $plainNote = $this->hydrator()->hydrate($plainDto, 'https://example.com/actor', 'https://example.com/actor/followers', true);
        $locationNote = $this->hydrator()->hydrate($locationDto, 'https://example.com/actor', 'https://example.com/actor/followers', true);

        $plainContext = $plainNote->toArray()['@context'];
        $locationContext = $locationNote->toArray()['@context'];

        $this->assertFalse($this->contextHasRtbTerm($plainContext));
        $this->assertTrue($this->contextHasRtbTerm($locationContext));
    }

    /**
     * Full circle: hydrate a local LocationPost into a Note, run its rtbExtension
     * through the defensive parser exactly as an incoming Create(Note) would, store it
     * on a fake remote ActivityPubPost, then reconstruct via ActivityPubPostHydrator
     */
    public function test_location_post_round_trips_through_parse_and_reconstruct(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create(['name' => 'Berlin Hbf']);
        LocationTag::create(['location_id' => $location->id, 'key' => 'addr:city', 'value' => 'Berlin']);

        $repo = new PostRepository;
        $visitedAt = Carbon::parse('2026-01-01 10:00:00');
        $created = $repo->storeLocation($user, $location, Visibility::PUBLIC, 'My own words about this stop', [], TravelReason::LEISURE, $visitedAt);
        $localDto = $repo->getById($created->id, null);

        $note = $this->hydrator()->hydrate($localDto, 'https://example.com/actor', 'https://example.com/actor/followers');
        $noteArray = $note->toArray();

        $parser = new ActivityPubExtensionParser;
        $parsed = $parser->parse($noteArray);
        $this->assertNotNull($parsed);

        $actor = ActivityPubActor::factory()->create();
        $remotePost = ActivityPubPost::create([
            'id' => Str::uuid(),
            'activity_pub_actor_id' => $actor->id,
            'activity_id' => 'https://remote.example/notes/'.Str::uuid(),
            'content' => $noteArray['content'],
            'extension_data' => $parsed,
            'published_at' => now(),
        ]);

        $apHydrator = new ActivityPubPostHydrator;
        $remoteDto = $apHydrator->modelToDto($remotePost);

        $this->assertInstanceOf(LocationPost::class, $remoteDto);
        $this->assertSame($localDto->location->id, $remoteDto->location->id);
        $this->assertSame($localDto->location->name, $remoteDto->location->name);
        $this->assertSame($localDto->location->latitude, $remoteDto->location->latitude);
        $this->assertSame($localDto->location->longitude, $remoteDto->location->longitude);
        $this->assertSame($localDto->travelReason, $remoteDto->travelReason);
        $this->assertSame($localDto->visitedAt, $remoteDto->visitedAt);
        // The raw body travels through the rtbExtension (not the sender's flattened
        // Mastodon HTML in Note::$content), so the reconstructed DTO has the user's
        // original text
        $this->assertSame('My own words about this stop', $remoteDto->body);
        // Reconstructed DTO renders its own rich emoji/location body, same as the
        // sender did, rather than the sender's flattened HTML being blindly reused —
        // so the location name must appear exactly once, not duplicated.
        $this->assertStringContainsString('My own words about this stop', $remoteDto->getHtmlBody());
        $this->assertSame(1, substr_count($remoteDto->getHtmlBody(), 'Berlin Hbf'));
    }

    public function test_transport_post_round_trips_through_parse_and_reconstruct(): void
    {
        $user = User::factory()->create();
        $trip = TransportTrip::factory()->create(['line_name' => 'RE1', 'display_name' => 'RE1']);
        $originStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 0,
            'departure_time' => Carbon::parse('2026-01-01 10:00:00'),
            'departure_delay' => 0,
        ]);
        $destinationStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 1,
        ]);

        $repo = new PostRepository;
        $created = $repo->storeTransport($user, $trip, $originStop, $destinationStop, Visibility::PUBLIC, 'My own words about this trip');
        $localDto = $repo->getById($created->id, null);

        $note = $this->hydrator()->hydrate($localDto, 'https://example.com/actor', 'https://example.com/actor/followers');
        $noteArray = $note->toArray();

        $parser = new ActivityPubExtensionParser;
        $parsed = $parser->parse($noteArray);
        $this->assertNotNull($parsed);

        $actor = ActivityPubActor::factory()->create();
        $remotePost = ActivityPubPost::create([
            'id' => Str::uuid(),
            'activity_pub_actor_id' => $actor->id,
            'activity_id' => 'https://remote.example/notes/'.Str::uuid(),
            'content' => $noteArray['content'],
            'extension_data' => $parsed,
            'published_at' => now(),
        ]);

        $apHydrator = new ActivityPubPostHydrator;
        $remoteDto = $apHydrator->modelToDto($remotePost);

        $this->assertInstanceOf(TransportPost::class, $remoteDto);
        $this->assertSame($localDto->trip->displayName, $remoteDto->trip->displayName);
        $this->assertSame($localDto->trip->mode, $remoteDto->trip->mode);
        $this->assertSame($localDto->originStop->name, $remoteDto->originStop->name);
        $this->assertSame($localDto->destinationStop->name, $remoteDto->destinationStop->name);
        $this->assertSame($localDto->distance, $remoteDto->distance);
        $this->assertSame($localDto->duration, $remoteDto->duration);
        $this->assertSame('My own words about this trip', $remoteDto->body);
        $this->assertStringContainsString('My own words about this trip', $remoteDto->getHtmlBody());
        $this->assertSame(1, substr_count($remoteDto->getHtmlBody(), 'RE1'));
    }

    private function contextHasRtbTerm(array $context): bool
    {
        foreach ($context as $entry) {
            if (is_array($entry) && array_key_exists('rtb', $entry)) {
                return true;
            }
        }

        return false;
    }
}
