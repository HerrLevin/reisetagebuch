import { TransportMode, TravelReason, Visibility } from '@/types/enums';
import { LocationEntry, UserDto } from '@/types/index';
import {
    BasePost as ApiBasePost,
    LocationPost as ApiLocationPost,
    TransportPost as ApiTransportPost,
} from '../../types/Api.gen';

export type BasePost = {
    id: string;
    body: string;
    user: UserDto;
    visibility: Visibility;
    published_at: string;
    created_at: string;
    updated_at: string;
    hashTags: string[];
    likesCount: number;
    likedByUser: boolean;
    metaInfos: Record<string, string | string[]>;
    travelReason: TravelReason | null;
};

export type LocationPost = BasePost & {
    location: LocationEntry;
    travelReason: TravelReason | null;
};

export type TransportPost = BasePost & {
    originStop: Stop;
    destinationStop: Stop;
    trip: Trip;
    manualDepartureTime: string | null;
    manualArrivalTime: string | null;
    travelReason: TravelReason | null;
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
    foreignId: string | null;
    mode: TransportMode;
    lineName: string;
    displayName: string | null;
    routeLongName: string | null;
    tripShortName: string | null;
    routeColor: string | null;
    routeTextColor: string | null;
};

export const isLocationPost = (post: BasePost | null): post is LocationPost => {
    return post !== null && (post as LocationPost).location !== undefined;
};

export const isApiLocationPost = (
    post: ApiBasePost | ApiLocationPost | ApiTransportPost | null,
): post is ApiLocationPost => {
    return post !== null && (post as ApiLocationPost).location !== undefined;
};

export const isApiTransportPost = (
    post: ApiBasePost | null,
): post is ApiTransportPost => {
    return post !== null && (post as ApiTransportPost).originStop !== undefined;
};

export const isTransportPost = (
    post: BasePost | null,
): post is TransportPost => {
    return post !== null && (post as TransportPost).originStop !== undefined;
};
