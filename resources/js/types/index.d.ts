import { TransportMode } from '@/types/enums';
import { GeometryCollection } from 'geojson';
import { Config } from 'ziggy-js';

export interface User {
    id: string;
    name: string;
    username: string;
    email: string;
    email_verified_at?: string;
    settings: Settings | null;
}

export interface Settings {
    default_new_post_view: 'location' | 'departures' | 'text';
    motis_radius: number | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
    canRegister: boolean;
    canInvite: boolean;
};

export type TripDto = {
    duration: number;
    startTime: string;
    endTime: string;
    transfers: number;
    legs: LegDto[];
};

export type LegDto = {
    mode: TransportMode;
    from: StopPlace;
    to: StopPlace;
    duration: number;
    startTime: string | null;
    endTime: string | null;
    scheduledStartTime: string | null;
    scheduledEndTime: string | null;
    realTime: boolean;
    headSign: string;
    agencyName: string | null;
    agencyId: string | null;
    tripId: string;
    routeShortName: string;
    displayName: string | null;
    source: string;
    intermediateStops: StopPlace[];
};

export type DeparturesDto = {
    stop: StopDto;
    departures: DepartureDto[];
};

export type StopDto = {
    stopId: string;
    tripStopId: string | null;
    name: string;
    latitude: number;
    longitude: number;
    distance: number | null;
};

export type StopTime = {
    place: StopPlace;
    mode: TransportMode;
    realTime: boolean;
    headSign: string;
    agencyName: string | null;
    agencyId: string | null;
    tripId: string;
    displayName: string | null;
    routeShortName: string;
    source: string;
};

export type StopPlace = {
    name: string;
    stopId: string;
    tripStopId: string | null;
    latitude: number;
    longitude: number;
    arrival: string | null;
    departure: string | null;
    scheduledArrival: string | null;
    scheduledDeparture: string | null;
};

export type LocationEntry = {
    id: string;
    name: string;
    latitude: number;
    longitude: number;
    distance: ?number;
    tags: LocationTag[];
    identifiers: LocationIdentifier[];
};

export type LocationIdentifier = {
    type: string;
    identifier: string;
    origin: string;
};

export type RequestLocationDto = {
    fetched: number;
    toFetch: number;
    updatedAt: string;
    lastRequestedAt: string | null;
};

export type LocationHistoryDto = {
    id: string;
    name: string | null;
    latitude: number;
    longitude: number;
    type: string;
    timestamp: string;
};

export type TripHistoryEntryDto = {
    id: string;
    geometry: GeometryCollection | null;
};

export type LocationTag = {
    key: string;
    value: string;
};

export type UserDto = {
    id: string;
    name: string;
    username: string;
    avatar: string | null;
    header: string | null;
    bio: string | null;
    website: string | null;
    createdAt: string;
};

export type Invite = {
    id: string;
    createdAt: string | null;
    expiresAt: string | null;
    usedAt: string | null;
};

export type Area = {
    name: string;
    default: boolean;
    adminLevel: number;
};
