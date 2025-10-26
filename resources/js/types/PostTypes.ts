import { TransportMode, Visibility } from '@/types/enums';
import { LocationEntry, UserDto } from '@/types/index';

export type BasePost = {
    id: string;
    body: string;
    user: UserDto;
    visibility: Visibility;
    created_at: string;
    updated_at: string;
};

export type LocationPost = BasePost & {
    location: LocationEntry;
};

export type TransportPost = BasePost & {
    originStop: Stop;
    destinationStop: Stop;
    trip: Trip;
    manualDepartureTime: string | null;
    manualArrivalTime: string | null;
};

export type AllPosts = BasePost | LocationPost | TransportPost;

export type Stop = {
    id: string;
    name: string;
    location: LocationEntry;
    arrivalTime: string | null;
    departureTime: string | null;
    arrivalDelay: number | null;
    departureDelay: number | null;
};

export type Trip = {
    id: string;
    mode: TransportMode;
    lineName: string;
    displayName: string | null;
};

export const isLocationPost = (post: BasePost): post is LocationPost => {
    return (post as LocationPost).location !== undefined;
};

export const isTransportPost = (post: BasePost): post is TransportPost => {
    return (post as TransportPost).originStop !== undefined;
};
