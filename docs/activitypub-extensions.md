# ActivityPub extensions

reisetagebu.ch federates two structured post types — locations and transport
legs — as plain ActivityPub `Note` objects so any Mastodon-compatible server
can display them. Instances running reisetagebu.ch additionally understand an
`rtbExtension` field carried on the same `Note`, which holds the structured
data (coordinates, stop times, trip metadata, …) that the post's rendered
`content` only summarizes in prose.

This is **not** part of the app's public REST API (that one is documented via
OpenAPI — see [`/api/documentation`](/../api/documentation)). It's a
federation-only, JSON-LD vocabulary extension: an optional, additively-typed
envelope attached to the AP object graph itself.

## Envelope

```json
{
  "rtbVersion": 1,
  "postType": "location",
  "location": { "...": "..." }
}
```

- **`rtbVersion`** — currently always `1`. A receiving server that doesn't
  recognize the version drops the whole envelope and falls back to treating
  the post as a plain Note.
- **`postType`** — `location` or `transport`. Selects which sibling key
  (`location` or `transport`) carries the body.

The envelope is only present on posts that have one; a plain Mastodon-style
Note omits the key entirely rather than sending `null`.

### `@context`

When present, the Note's `@context` gains an extra entry declaring the `rtb`
namespace and marking `rtbExtension` as an opaque JSON-LD value, so generic
AP consumers that don't understand it just ignore it instead of choking on
unexpected JSON:

```json
[
  "https://www.w3.org/ns/activitystreams",
  {
    "rtb": "https://reisetagebu.ch/ns#",
    "rtbExtension": { "@id": "rtb:extension", "@type": "@json" }
  }
]
```

## Location posts (`postType: "location"`)

```json
{
  "rtbVersion": 1,
  "postType": "location",
  "location": {
    "id": "loc-1",
    "name": "Berlin Hbf",
    "latitude": 52.52,
    "longitude": 13.405,
    "timezone": "Europe/Berlin",
    "emoji": "🚉",
    "tags": [{ "key": "addr:city", "value": "Berlin" }],
    "identifiers": [{ "type": "stop", "origin": "motis", "identifier": "de:11000:900003201" }],
    "travelReason": "leisure",
    "visitedAt": "2026-01-01T10:00:00+00:00"
  }
}
```

`travelReason` and `visitedAt` describe the *visit*, not the place, but ride
alongside the location's own fields because there's nowhere else in this
envelope for post-level attributes to live.

## Transport posts (`postType: "transport"`)

```json
{
  "rtbVersion": 1,
  "postType": "transport",
  "transport": {
    "originStop": { "...": "a stop object, see below" },
    "destinationStop": { "...": "a stop object, see below" },
    "trip": {
      "id": "trip-1",
      "foreignId": "foreign-1",
      "mode": "RAIL",
      "lineName": "RE1",
      "routeLongName": null,
      "tripShortName": null,
      "displayName": "RE1",
      "routeColor": null,
      "routeTextColor": null
    },
    "manualDepartureTime": null,
    "manualArrivalTime": null,
    "travelReason": "commute",
    "distance": 12345,
    "duration": 600,
    "userGeometry": { "type": "LineString", "coordinates": [[13.4, 52.5]] }
  }
}
```

A stop (`originStop`/`destinationStop`) is a named point in time and space:

```json
{
  "id": "stop-1",
  "name": "Berlin Hbf",
  "location": { "...": "a location, WITHOUT travelReason/visitedAt" },
  "arrivalTime": null,
  "departureTime": "2026-01-01T10:00:00+00:00",
  "arrivalDelay": null,
  "departureDelay": 0
}
```

Note that a stop's `location` is the bare location object — `travelReason`
and `visitedAt` only ever appear in the top-level `location` body of a
location post, never nested inside a stop.

## Validating the envelope

[`resources/schemas/activitypub/rtb-extension.v1.schema.json`](../resources/schemas/activitypub/rtb-extension.v1.schema.json)
is a JSON Schema (2020-12) formalizing everything above: the envelope's
`postType`-driven dispatch, every field on `location`/`transport`/`stop`/
`trip`, and the closed `TravelReason`/`TransportMode` enumerations. It's kept
in sync with the implementation by
`tests/Unit/Dto/ActivityPub/Extensions/RtbExtensionSchemaTest.php`, which
validates real `RtbLocationExtension`/`RtbTransportExtension` output (and a
handful of deliberately malformed payloads) against it on every test run.

Any JSON Schema validator can use the file directly, for example to sanity
check a captured federation payload:

```bash
npx ajv-cli validate -s resources/schemas/activitypub/rtb-extension.v1.schema.json -d payload.json
```

## Why no OpenAPI here

The rest of the app's data shapes (`LocationDto`, `StopDto`, `TripDto`, …)
are documented with `zircote/swagger-php` attributes because they're request/
response bodies on documented REST endpoints. The `rtbExtension` envelope
isn't an endpoint's request or response — it's a nested value inside a
federated `Note`, parsed defensively from arbitrary remote servers (see
`App\Services\ActivityPubExtensionParser` and
`App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields`). OpenAPI has
no good way to express "this key may or may not exist on an object defined by
someone else's spec"; JSON Schema plus this narrative doc is the better fit.

## Parsing philosophy

Every `Rtb*Data::fromArray()` is built from untrusted, federated JSON and
degrades field-by-field: an individual missing/invalid field falls back to
null/empty rather than throwing, while a handful of structurally required
fields (coordinates on a location, the nested stops/trip on a transport post)
cause the *whole* object to be rejected (`fromArray()` returns `null`). See
`RtbExtensionFactory::fromArray()` for the single envelope-level dispatch
point shared by incoming federation (`ActivityPubExtensionParser`) and our
own stored posts (`RtbExtensionCast`).
