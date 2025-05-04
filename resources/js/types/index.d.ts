import { TransportMode } from '@/types/enums';
import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
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
    source: string;
    intermediateStops: StopPlace[];
};

export type DeparturesDto = {
    stop: StopDto;
    departures: DepartureDto[];
};

export type StopDto = {
    stopId: string;
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
    routeShortName: string;
    source: string;
};

export type StopPlace = {
    name: string;
    stopId: string;
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
};

export type LocationTag = {
    key: string;
    value: string;
};

export type UserDto = {
    id: string;
    name: string;
    username: string;
    createdAt: string;
};
