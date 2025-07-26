<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string|null $used_by
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $usedBy
 * @method static \Database\Factories\InviteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite whereUsedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invite whereUserId($value)
 */
	class Invite extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $type
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Clickbar\Magellan\Data\Geometries\Point $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LocationIdentifier> $identifiers
 * @property-read int|null $identifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LocationTag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $location_id
 * @property string $type
 * @property string $identifier
 * @property string $origin
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereUpdatedAt($value)
 */
	class LocationIdentifier extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $post_id
 * @property string $location_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereUpdatedAt($value)
 */
	class LocationPost extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $location_id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereValue($value)
 */
	class LocationTag extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string|null $body
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LocationPost|null $locationPost
 * @property-read \App\Models\TransportPost|null $transportPost
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUserId($value)
 */
	class Post extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string|null $avatar
 * @property string|null $bio
 * @property string|null $website
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $header
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereWebsite($value)
 */
	class Profile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string|null $last_requested_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Clickbar\Magellan\Data\Geometries\Point $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereLastRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereUpdatedAt($value)
 */
	class RequestLocation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $from_location_id
 * @property string $to_location_id
 * @property int $distance Distance in meters
 * @property int|null $duration Duration in seconds
 * @property string|null $path_type Type of path, e.g., rail, road, trail
 * @property \Clickbar\Magellan\Data\Geometries\LineString $geometry Geospatial data representing the route segment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $fromLocation
 * @property-read \App\Models\Location $toLocation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereFromLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereGeometry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment wherePathType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereToLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereUpdatedAt($value)
 */
	class RouteSegment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property string $provider
 * @property string $provider_user_id
 * @property string $access_token
 * @property string|null $refresh_token
 * @property \Illuminate\Support\Carbon|null $token_expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereProviderUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereTokenExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereUserId($value)
 */
	class SocialAccount extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property \Clickbar\Magellan\Data\Geometries\Point|null $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimestampedUserWaypoint whereUserId($value)
 */
	class TimestampedUserWaypoint extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $post_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $transport_trip_id
 * @property string $origin_stop_id
 * @property string $destination_stop_id
 * @property-read \App\Models\Location|null $destination
 * @property-read \App\Models\TransportTripStop $destinationStop
 * @property-read \App\Models\Location|null $origin
 * @property-read \App\Models\TransportTripStop $originStop
 * @property-read \App\Models\TransportTrip $transportTrip
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereDestinationStopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereOriginStopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereTransportTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereUpdatedAt($value)
 */
	class TransportPost extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string|null $foreign_trip_id Unique identifier for the trip in the external system
 * @property string|null $provider Name of the data provider, e.g., "TransportAPI"
 * @property string $mode Transport mode, e.g., "bus", "train", "car"
 * @property string|null $line_name Name of the transport line, e.g., "Line 1", "Route A"
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransportTripStop> $stops
 * @property-read int|null $stops_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereForeignTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereLineName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereUpdatedAt($value)
 */
	class TransportTrip extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $transport_trip_id
 * @property string $location_id
 * @property \Illuminate\Support\Carbon|null $arrival_time Arrival time at the stop, in UTC
 * @property \Illuminate\Support\Carbon|null $departure_time Departure time from the stop, in UTC
 * @property int|null $arrival_delay Delay in seconds at arrival, 0 if on time, null if not applicable
 * @property int|null $departure_delay Delay in seconds at departure, 0 if on time, null if not applicable
 * @property int $stop_sequence Sequence number of the stop in the trip, starting from 0
 * @property bool $cancelled Indicates if the stop was cancelled, true if cancelled, false otherwise
 * @property string|null $route_segment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @property-read \App\Models\RouteSegment|null $routeSegment
 * @property-read \App\Models\TransportTrip $transportTrip
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereArrivalDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereArrivalTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereDepartureDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereDepartureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereRouteSegmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereStopSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereTransportTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereUpdatedAt($value)
 */
	class TransportTripStop extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Enums\DefaultNewPostView $default_new_post_view
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invite> $invites
 * @property-read int|null $invites_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimestampedUserWaypoint> $locationHistory
 * @property-read int|null $location_history_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $posts
 * @property-read int|null $posts_count
 * @property-read \App\Models\Profile|null $profile
 * @property-read \App\Models\UserSettings|null $settings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SocialAccount> $socialAccounts
 * @property-read int|null $social_accounts_count
 * @property-read \App\Models\SocialAccount|null $traewellingAccount
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $user_id
 * @property \App\Enums\DefaultNewPostView $default_new_post_view Default view for new posts, e.g., location, departures, post
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereDefaultNewPostView($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSettings whereUserId($value)
 */
	class UserSettings extends \Eloquent {}
}

