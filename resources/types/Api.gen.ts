/* eslint-disable */
/* tslint:disable */
// @ts-nocheck
/*
 * ---------------------------------------------------------------
 * ## THIS FILE WAS GENERATED VIA SWAGGER-TYPESCRIPT-API        ##
 * ##                                                           ##
 * ## AUTHOR: acacode                                           ##
 * ## SOURCE: https://github.com/acacode/swagger-typescript-api ##
 * ---------------------------------------------------------------
 */

/** Visibility levels for posts */
export enum Visibility {
    Public = 'public',
    Private = 'private',
    Unlisted = 'unlisted',
    OnlyAuthenticated = 'only-authenticated',
}

/** Enumeration of transport modes */
export enum TransportMode {
    WALK = 'WALK',
    BIKE = 'BIKE',
    RENTAL = 'RENTAL',
    CAR = 'CAR',
    CAR_PARKING = 'CAR_PARKING',
    CAR_DROPOFF = 'CAR_DROPOFF',
    ODM = 'ODM',
    FLEX = 'FLEX',
    TRANSIT = 'TRANSIT',
    TRAM = 'TRAM',
    SUBWAY = 'SUBWAY',
    FERRY = 'FERRY',
    AIRPLANE = 'AIRPLANE',
    SUBURBAN = 'SUBURBAN',
    BUS = 'BUS',
    COACH = 'COACH',
    RAIL = 'RAIL',
    HIGHSPEED_RAIL = 'HIGHSPEED_RAIL',
    LONG_DISTANCE = 'LONG_DISTANCE',
    NIGHT_RAIL = 'NIGHT_RAIL',
    REGIONAL_FAST_RAIL = 'REGIONAL_FAST_RAIL',
    REGIONAL_RAIL = 'REGIONAL_RAIL',
    CABLE_CAR = 'CABLE_CAR',
    FUNICULAR = 'FUNICULAR',
    AERIAL_LIFT = 'AERIAL_LIFT',
    OTHER = 'OTHER',
    AREAL_LIFT = 'AREAL_LIFT',
    METRO = 'METRO',
}

/** Enum representing different travel roles */
export enum TravelRole {
    Deadhead = 'deadhead',
    Operator = 'operator',
    Catering = 'catering',
}

/** Reasons for travel associated with transport posts */
export enum TravelReason {
    Commute = 'commute',
    Business = 'business',
    Leisure = 'leisure',
    Crew = 'crew',
    Errand = 'errand',
    Other = 'other',
}

export enum MotisLocationType {
    ADDRESS = 'ADDRESS',
    PLACE = 'PLACE',
    STOP = 'STOP',
}

/** Data Transfer Object for Departures at a Stop */
export interface DeparturesDto {
    /** Data Transfer Object for a Stop */
    stop: MotisStopDto;
    /** Collection of departure stop times */
    departures: any[];
}

/** Data Transfer Object for Departures Response */
export interface DeparturesResponseDto {
    /** Data Transfer Object for Departures at a Stop */
    departures: DeparturesDto;
    /** Filter modes applied to the request */
    modes: TransportMode[];
    /**
     * Time when the request was made
     * @format date-time
     */
    requestTime: string;
    /** Unique identifier for the request */
    requestIdentifier: string | null;
    /**
     * Latitude of the request location
     * @format float
     */
    requestLatitude: number;
    /**
     * Longitude of the request location
     * @format float
     */
    requestLongitude: number;
}

/** Pagination DTO specifically for posts */
export type FilteredPostPaginationDto = PostPaginationDto & {
    availableTags: string[];
    /** Array of post items on the current page */
    items: (LocationPost | BasePost | TransportPost)[];
};

export interface LikeResponseDto {
    /** Indicates if the post is liked by the user */
    likedByUser: boolean;
    /** Total number of likes on the post */
    likeCount: number;
}

/** Data Transfer Object for Location History */
export interface LocationHistoryDto {
    /** Collection of location history entries */
    locations: LocationHistoryEntryDto[];
    /** Collection of trip history entries */
    trips: TripHistoryEntryDto[];
}

export interface MotisGeocodeResponseEntry {
    type: MotisLocationType;
    /** An array of tokens representing parts of the location name or identifier */
    tokens: string[];
    /** The name of the location */
    name: string;
    /** A unique identifier for the location */
    identifier: string;
    /** The ID of the location */
    id: string | null;
    /**
     * The latitude of the location
     * @format float
     */
    lat: number;
    /**
     * The longitude of the location
     * @format float
     */
    lon: number;
    /** The level or floor of the location, if applicable */
    level: string | null;
    /** The street name of the location, if applicable */
    street: string | null;
    /** The house number of the location, if applicable */
    houseNumber: string | null;
    /** The ZIP code of the location, if applicable */
    zip: string | null;
    /** An array of area names or identifiers associated with the location */
    areas: string[];
    /**
     * The confidence score of the geocode result
     * @format float
     */
    score: number;
    /** An array of location identifiers */
    identifiers: LocationIdentifierDto[];
}

/** Data Transfer Object for a Stop */
export interface MotisStopDto {
    /** Unique identifier for the stop */
    stopId: string;
    /** Trip-specific identifier for the stop */
    tripStopId: string | null;
    /** Name of the stop */
    name: string;
    /**
     * Latitude of the stop
     * @format float
     */
    latitude: number;
    /**
     * Longitude of the stop
     * @format float
     */
    longitude: number;
    /** Distance to the stop in meters */
    distance: number | null;
}

/** A generic pagination DTO */
export interface PaginationDto {
    /** Number of items per page */
    perPage: number;
    /** Cursor for the next page */
    nextCursor: string | null;
    /** Cursor for the previous page */
    previousCursor: string | null;
}

/** Pagination DTO specifically for posts */
export type PostPaginationDto = PaginationDto & {
    /** Array of post items on the current page */
    items: (LocationPost | BasePost | TransportPost)[];
};

/** Data Transfer Object for Stopovers Response */
export interface StopoversResponseDto {
    trip: any;
    /**
     * Start time of the trip
     * @format date-time
     */
    startTime: string;
    /** Identifier for the start location */
    startId: string;
    /** Unique identifier for the trip */
    tripId: string;
}

/** Data Transfer Object for Trip Creation Response */
export interface TripCreationResponseDto {
    /**
     * The identifier of the created trip (foreign trip id)
     * @example "trip_12345"
     */
    tripId: string;
    /**
     * The identifier of the starting location of the trip
     * @example "loc_67890"
     */
    startId: string;
    /**
     * The departure time of the trip in ISO 8601 format
     * @example "2024-07-01T10:00:00Z"
     */
    startTime: string;
}

/**
 * Profile Update Request
 * Request schema for updating user profile information
 */
export interface ProfileUpdateRequest {
    /**
     * The full name of the user
     * @maxLength 255
     * @example "John Doe"
     */
    name: string;
    /**
     * The unique username for the user (lowercase, alphanumeric, dashes, and underscores only)
     * @maxLength 30
     * @example "john_doe"
     */
    username: string;
    /**
     * The email address of the user (lowercase)
     * @format email
     * @maxLength 255
     * @example "mail@example.com"
     */
    email: string;
}

/** Request to update user settings */
export interface SettingsUpdateRequest {
    /** Radius for Motis suggestions in meters (allowed values: 50, 100, 200, 500) */
    motisRadius?: number | null;
}

export interface StoreInviteCodeRequest {
    /**
     * The expiration date of the invite code
     * @format date-time
     */
    expires_at?: string | null;
}

export interface StoreTripRequest {
    /** The mode of transport for the trip. */
    mode:
        | 'WALK'
        | 'BIKE'
        | 'RENTAL'
        | 'CAR'
        | 'CAR_PARKING'
        | 'CAR_DROPOFF'
        | 'ODM'
        | 'FLEX'
        | 'TRANSIT'
        | 'TRAM'
        | 'SUBWAY'
        | 'FERRY'
        | 'AIRPLANE'
        | 'SUBURBAN'
        | 'BUS'
        | 'COACH'
        | 'RAIL'
        | 'HIGHSPEED_RAIL'
        | 'LONG_DISTANCE'
        | 'NIGHT_RAIL'
        | 'REGIONAL_FAST_RAIL'
        | 'REGIONAL_RAIL'
        | 'CABLE_CAR'
        | 'FUNICULAR'
        | 'AERIAL_LIFT'
        | 'OTHER'
        | 'AREAL_LIFT'
        | 'METRO';
    /** The name of the line. */
    lineName?: string | null;
    /** The long name of the route. */
    routeLongName?: string | null;
    /** The short name of the trip. */
    tripShortName?: string | null;
    /** The display name of the trip. */
    displayName?: string | null;
    /** The origin location of the trip. */
    origin: string;
    /** The type of identifier used for the origin (id or identifier). */
    originType?: string | null;
    /** The destination location of the trip. */
    destination: string;
    /** The type of identifier used for the destination (id or identifier). */
    destinationType?: string | null;
    /**
     * The departure time of the trip.
     * @format date-time
     */
    departureTime: string;
    /**
     * The arrival time of the trip.
     * @format date-time
     */
    arrivalTime: string;
    /** An array of stops for the trip. */
    stops?: {
        /** The identifier of the stop. */
        identifier?: string;
        /** The type of identifier used for the stop (id or identifier). */
        identifierType?: string | null;
        /** The order of the stop in the trip sequence. */
        order?: number;
    }[];
}

export interface TransportPostExitUpdateRequest {
    /**
     * The ID of the transport trip stop, the user wants to exit at.
     * @example "123e4567-e89b-12d3-a456-426614174000"
     */
    stopId: string;
}

export interface TransportTimesUpdateRequest {
    /**
     * The manually set departure time for the transport post.
     * @format date-time
     * @example "2024-08-01T10:00:00Z"
     */
    manualDepartureTime?: string | null;
    /**
     * The manually set arrival time for the transport post.
     * @format date-time
     * @example "2024-08-01T12:00:00Z"
     */
    manualArrivalTime?: string | null;
}

/** Request to update user profile */
export interface UpdateProfileRequest {
    /**
     * Full name of the user
     * @maxLength 255
     */
    name: string;
    /**
     * Biography of the user
     * @maxLength 500
     */
    bio?: string | null;
    /**
     * Website URL of the user
     * @format uri
     * @maxLength 255
     */
    website?: string | null;
    /**
     * Avatar image file upload
     * @format binary
     */
    avatar?: File | null;
    /**
     * Header image file upload
     * @format binary
     */
    header?: File | null;
    /** Flag to delete existing avatar */
    deleteAvatar?: boolean | null;
    /** Flag to delete existing header */
    deleteHeader?: boolean | null;
}

export interface InviteDto {
    /** The unique identifier of the invite code */
    id: string;
    /**
     * The creation timestamp of the invite code
     * @format date-time
     */
    createdAt: string | null;
    /**
     * The expiration timestamp of the invite code
     * @format date-time
     */
    expiresAt: string | null;
    /**
     * The timestamp when the invite code was used
     * @format date-time
     */
    usedAt: string | null;
}

/** Location Data Object */
export interface LocationDto {
    /**
     * Location ID
     * @format uuid
     */
    id: string;
    /** Name of the location */
    name: string;
    /**
     * Latitude of the location
     * @format float
     */
    latitude: number;
    /**
     * Longitude of the location
     * @format float
     */
    longitude: number;
    /** Distance to the location in meters */
    distance: number | null;
    /** List of location identifiers */
    identifiers: LocationIdentifierDto[];
    /** List of location tags */
    tags: LocationTagDto[];
}

/** Location History Entry Data Transfer Object */
export interface LocationHistoryEntryDto {
    /**
     * Location ID
     * @format uuid
     */
    id: string;
    /** Name of the location */
    name: string | null;
    /**
     * Latitude of the location
     * @format float
     */
    latitude: number;
    /**
     * Longitude of the location
     * @format float
     */
    longitude: number;
    /** Type of the location history entry */
    type: string;
    /**
     * Timestamp of the location history entry in ISO 8601 format
     * @format date-time
     */
    timestamp: string;
}

/** Location Identifier Data Object */
export interface LocationIdentifierDto {
    /** Type of the location identifier */
    type: string;
    /** Origin of the location identifier */
    origin: string;
    /** The location identifier value */
    identifier: string;
}

/** Location Tag Data Object */
export interface LocationTagDto {
    /** Key of the location tag */
    key: string;
    /** Value of the location tag */
    value: string;
}

/** Base Post Resource */
export interface BasePost {
    /**
     * Post ID
     * @format uuid
     */
    id: string;
    /** User Data Object */
    user: UserDto;
    /** Post body content */
    body: string | null;
    /** Visibility levels for posts */
    visibility: Visibility;
    /**
     * Post published at timestamp
     * @format date-time
     */
    publishedAt: string;
    /**
     * Post created at timestamp
     * @format date-time
     */
    createdAt: string;
    /**
     * Post updated at timestamp
     * @format date-time
     */
    updatedAt: string;
    /** List of hashtags associated with the post */
    hashTags: string[];
    /** Number of likes on the post */
    likesCount: number;
    /** Indicates if the post is liked by the current user */
    likedByUser: boolean;
    /** Additional meta information associated with the post */
    metaInfos: Record<string, string | string[]>;
}

/** Location Post Resource */
export type LocationPost = BasePost & {
    /** Location Data Object */
    location: LocationDto;
    /** Reason for travel associated with the location post */
    travelReason: TravelReason | null;
};

/** Transport Post Resource */
export type TransportPost = BasePost & {
    /** Data Transfer Object for a Transport Trip Stop */
    originStop: StopDto;
    /** Data Transfer Object for a Transport Trip Stop */
    destinationStop: StopDto;
    /** Data Transfer Object for a Transport Trip */
    trip: TripDto;
    /**
     * Manually specified departure time in ISO 8601 format
     * @format date-time
     */
    manualDepartureTime: string | null;
    /**
     * Manually specified arrival time in ISO 8601 format
     * @format date-time
     */
    manualArrivalTime: string | null;
    /** Reason for travel associated with the transport post */
    travelReason: TravelReason | null;
};

/** Data Transfer Object for a Transport Trip Stop */
export interface StopDto {
    /**
     * Unique identifier for the stop
     * @format uuid
     */
    id: string;
    /** Name of the stop */
    name: string;
    /** Location Data Object */
    location: LocationDto;
    /**
     * Scheduled arrival time in ISO 8601 format
     * @format date-time
     */
    arrivalTime: string | null;
    /**
     * Scheduled departure time in ISO 8601 format
     * @format date-time
     */
    departureTime: string | null;
    /** Arrival delay in minutes */
    arrivalDelay: number | null;
    /** Departure delay in minutes */
    departureDelay: number | null;
}

/** Data Transfer Object for a Transport Trip */
export interface TripDto {
    /**
     * Unique identifier for the trip
     * @format uuid
     */
    id: string;
    /** Foreign identifier for the trip */
    foreignId: string | null;
    /** Enumeration of transport modes */
    mode: TransportMode;
    /** Name of the line */
    lineName: string | null;
    /** Long name of the route */
    routeLongName: string | null;
    /** Short name of the trip */
    tripShortName: string | null;
    /** Display name of the trip */
    displayName: string | null;
    /** Color of the route in HEX format */
    routeColor: string | null;
    /** Text color of the route in HEX format */
    routeTextColor: string | null;
}

/** Trip History Entry Data Transfer Object */
export interface TripHistoryEntryDto {
    /**
     * Transport Post ID
     * @format uuid
     */
    id: string;
    /** Geometry of the trip as a GeoJSON LineString */
    geometry: object | null;
    /** Data Transfer Object for a Transport Trip */
    trip: TripDto;
}

/** User Data Object */
export interface UserDto {
    /**
     * User ID
     * @format uuid
     */
    id: string;
    /** Full name of the user */
    name: string;
    /** Username of the user */
    username: string;
    /**
     * URL of the user avatar image
     * @format uri
     */
    avatar: string | null;
    /**
     * URL of the user header image
     * @format uri
     */
    header: string | null;
    /** Biography of the user */
    bio: string | null;
    /**
     * Website URL of the user
     * @format uri
     */
    website: string | null;
    /**
     * Account creation timestamp
     * @format date-time
     */
    createdAt: string;
}

import type {
    AxiosInstance,
    AxiosRequestConfig,
    AxiosResponse,
    HeadersDefaults,
    ResponseType,
} from 'axios';
import axios from 'axios';

export type QueryParamsType = Record<string | number, any>;

export interface FullRequestParams extends Omit<
    AxiosRequestConfig,
    'data' | 'params' | 'url' | 'responseType'
> {
    /** set parameter to `true` for call `securityWorker` for this request */
    secure?: boolean;
    /** request path */
    path: string;
    /** content type of request body */
    type?: ContentType;
    /** query params */
    query?: QueryParamsType;
    /** format of response (i.e. response.json() -> format: "json") */
    format?: ResponseType;
    /** request body */
    body?: unknown;
}

export type RequestParams = Omit<
    FullRequestParams,
    'body' | 'method' | 'query' | 'path'
>;

export interface ApiConfig<SecurityDataType = unknown> extends Omit<
    AxiosRequestConfig,
    'data' | 'cancelToken'
> {
    securityWorker?: (
        securityData: SecurityDataType | null,
    ) => Promise<AxiosRequestConfig | void> | AxiosRequestConfig | void;
    secure?: boolean;
    format?: ResponseType;
}

export enum ContentType {
    Json = 'application/json',
    JsonApi = 'application/vnd.api+json',
    FormData = 'multipart/form-data',
    UrlEncoded = 'application/x-www-form-urlencoded',
    Text = 'text/plain',
}

export class HttpClient<SecurityDataType = unknown> {
    public instance: AxiosInstance;
    private securityData: SecurityDataType | null = null;
    private securityWorker?: ApiConfig<SecurityDataType>['securityWorker'];
    private secure?: boolean;
    private format?: ResponseType;

    constructor({
        securityWorker,
        secure,
        format,
        ...axiosConfig
    }: ApiConfig<SecurityDataType> = {}) {
        this.instance = axios.create({
            ...axiosConfig,
            baseURL: axiosConfig.baseURL || 'http://localhost/api',
        });
        this.secure = secure;
        this.format = format;
        this.securityWorker = securityWorker;
    }

    public setSecurityData = (data: SecurityDataType | null) => {
        this.securityData = data;
    };

    protected mergeRequestParams(
        params1: AxiosRequestConfig,
        params2?: AxiosRequestConfig,
    ): AxiosRequestConfig {
        const method = params1.method || (params2 && params2.method);

        return {
            ...this.instance.defaults,
            ...params1,
            ...(params2 || {}),
            headers: {
                ...((method &&
                    this.instance.defaults.headers[
                        method.toLowerCase() as keyof HeadersDefaults
                    ]) ||
                    {}),
                ...(params1.headers || {}),
                ...((params2 && params2.headers) || {}),
            },
        };
    }

    protected stringifyFormItem(formItem: unknown) {
        if (typeof formItem === 'object' && formItem !== null) {
            return JSON.stringify(formItem);
        } else {
            return `${formItem}`;
        }
    }

    protected createFormData(input: Record<string, unknown>): FormData {
        if (input instanceof FormData) {
            return input;
        }
        return Object.keys(input || {}).reduce((formData, key) => {
            const property = input[key];
            const propertyContent: any[] =
                property instanceof Array ? property : [property];

            for (const formItem of propertyContent) {
                const isFileType =
                    formItem instanceof Blob || formItem instanceof File;
                formData.append(
                    key,
                    isFileType ? formItem : this.stringifyFormItem(formItem),
                );
            }

            return formData;
        }, new FormData());
    }

    public request = async <T = any, _E = any>({
        secure,
        path,
        type,
        query,
        format,
        body,
        ...params
    }: FullRequestParams): Promise<AxiosResponse<T>> => {
        const secureParams =
            ((typeof secure === 'boolean' ? secure : this.secure) &&
                this.securityWorker &&
                (await this.securityWorker(this.securityData))) ||
            {};
        const requestParams = this.mergeRequestParams(params, secureParams);
        const responseFormat = format || this.format || undefined;

        if (
            type === ContentType.FormData &&
            body &&
            body !== null &&
            typeof body === 'object'
        ) {
            body = this.createFormData(body as Record<string, unknown>);
        }

        if (
            type === ContentType.Text &&
            body &&
            body !== null &&
            typeof body !== 'string'
        ) {
            body = JSON.stringify(body);
        }

        return this.instance.request({
            ...requestParams,
            headers: {
                ...(requestParams.headers || {}),
                ...(type ? { 'Content-Type': type } : {}),
            },
            params: query,
            responseType: responseFormat,
            data: body,
            url: path,
        });
    };
}

/**
 * @title Reisetagebuch API Draft
 * @version 0.1.0-draft
 * @license MIT (https://opensource.org/licenses/MIT)
 * @baseUrl http://localhost/api
 *
 * This is a draft version of the Reisetagebuch API documentation. The API is still under development and may change in future releases. Use at your own risk!
 */
export class Api<
    SecurityDataType extends unknown,
> extends HttpClient<SecurityDataType> {
    account = {
        /**
         * @description Delete the authenticated account
         *
         * @tags Account
         * @name DeleteAccount
         * @summary Delete account
         * @request DELETE:/account
         * @secure
         */
        deleteAccount: (
            data: {
                /**
                 * Current password of the account
                 * @example "your-current-password"
                 */
                password?: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/account`,
                method: 'DELETE',
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),

        /**
         * @description Update account details
         *
         * @tags Account
         * @name UpdateAccount
         * @summary Update account
         * @request PATCH:/account
         * @secure
         */
        updateAccount: (
            data: ProfileUpdateRequest,
            params: RequestParams = {},
        ) =>
            this.request<void, any>({
                path: `/account`,
                method: 'PATCH',
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),

        /**
         * @description Disconnect Traewelling from account
         *
         * @tags Account
         * @name DisconnectTraewelling
         * @summary Disconnect Traewelling
         * @request DELETE:/account/socialite/traewelling
         * @secure
         */
        disconnectTraewelling: (params: RequestParams = {}) =>
            this.request<void, void>({
                path: `/account/socialite/traewelling`,
                method: 'DELETE',
                secure: true,
                ...params,
            }),

        /**
         * @description Update profile for authenticated user
         *
         * @tags Profile
         * @name UpdateProfile
         * @summary Update profile
         * @request POST:/account/profile
         */
        updateProfile: (
            data: UpdateProfileRequest,
            params: RequestParams = {},
        ) =>
            this.request<UserDto, any>({
                path: `/account/profile`,
                method: 'POST',
                body: data,
                type: ContentType.FormData,
                format: 'json',
                ...params,
            }),

        /**
         * @description Update user settings
         *
         * @tags Account
         * @name UpdateSettings
         * @summary Update settings
         * @request PATCH:/account/settings
         * @secure
         */
        updateSettings: (
            data: SettingsUpdateRequest,
            params: RequestParams = {},
        ) =>
            this.request<void, any>({
                path: `/account/settings`,
                method: 'PATCH',
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),
    };
    invites = {
        /**
         * @description List invite codes for the authenticated user
         *
         * @tags Invites
         * @name ListInvites
         * @summary List invites
         * @request GET:/invites
         * @secure
         */
        listInvites: (params: RequestParams = {}) =>
            this.request<InviteDto[], void>({
                path: `/invites`,
                method: 'GET',
                secure: true,
                format: 'json',
                ...params,
            }),

        /**
         * @description Create a new invite code
         *
         * @tags Invites
         * @name CreateInvite
         * @summary Create invite
         * @request POST:/invites
         * @secure
         */
        createInvite: (
            data: StoreInviteCodeRequest,
            params: RequestParams = {},
        ) =>
            this.request<void, void>({
                path: `/invites`,
                method: 'POST',
                body: data,
                secure: true,
                type: ContentType.Json,
                ...params,
            }),

        /**
         * @description Delete an invite code
         *
         * @tags Invites
         * @name DeleteInvite
         * @summary Delete invite
         * @request DELETE:/invites/{inviteCode}
         * @secure
         */
        deleteInvite: (inviteCode: string, params: RequestParams = {}) =>
            this.request<void, void>({
                path: `/invites/${inviteCode}`,
                method: 'DELETE',
                secure: true,
                ...params,
            }),
    };
    posts = {
        /**
         * @description Like a post
         *
         * @tags Posts
         * @name LikePost
         * @summary Like post
         * @request POST:/posts/{post}/likes
         */
        likePost: (post: string, params: RequestParams = {}) =>
            this.request<LikeResponseDto, void>({
                path: `/posts/${post}/likes`,
                method: 'POST',
                format: 'json',
                ...params,
            }),

        /**
         * @description Remove like from a post
         *
         * @tags Posts
         * @name UnlikePost
         * @summary Unlike post
         * @request DELETE:/posts/{post}/likes
         */
        unlikePost: (post: string, params: RequestParams = {}) =>
            this.request<LikeResponseDto, void>({
                path: `/posts/${post}/likes`,
                method: 'DELETE',
                format: 'json',
                ...params,
            }),

        /**
         * @description Returns post data
         *
         * @tags Posts
         * @name ShowPost
         * @summary Get post by ID
         * @request GET:/posts/{id}
         * @secure
         */
        showPost: (id: string, params: RequestParams = {}) =>
            this.request<BasePost | TransportPost | LocationPost, void>({
                path: `/posts/${id}`,
                method: 'GET',
                secure: true,
                format: 'json',
                ...params,
            }),

        /**
         * @description Update a post (text, transport or location)
         *
         * @tags Posts
         * @name UpdatePost
         * @summary Update post
         * @request PUT:/posts/{id}
         * @secure
         */
        updatePost: (id: string, data: any, params: RequestParams = {}) =>
            this.request<BasePost | TransportPost | LocationPost, void>({
                path: `/posts/${id}`,
                method: 'PUT',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),

        /**
         * @description Delete a post by id
         *
         * @tags Posts
         * @name DeletePost
         * @summary Delete post
         * @request DELETE:/posts/{id}
         * @secure
         */
        deletePost: (id: string, params: RequestParams = {}) =>
            this.request<void, void>({
                path: `/posts/${id}`,
                method: 'DELETE',
                secure: true,
                ...params,
            }),

        /**
         * @description Returns filtered posts
         *
         * @tags Posts
         * @name FilterPosts
         * @summary Filter posts
         * @request GET:/posts
         * @secure
         */
        filterPosts: (
            query?: {
                /** Pagination cursor */
                cursor?: string;
                /**
                 * Filter posts from this date (YYYY-MM-DD)
                 * @format date
                 */
                dateFrom?: string;
                /**
                 * Filter posts to this date (YYYY-MM-DD)
                 * @format date
                 */
                dateTo?: string;
                /** Filter by visibility (e.g., PUBLIC, PRIVATE) */
                visibility?: Visibility[];
                /** Filter by travel reason (e.g., WORK, LEISURE) */
                travelReason?: TravelReason[];
                /** Filter by tags */
                tags?: string[];
            },
            params: RequestParams = {},
        ) =>
            this.request<FilteredPostPaginationDto, void>({
                path: `/posts`,
                method: 'GET',
                query: query,
                secure: true,
                format: 'json',
                ...params,
            }),

        /**
         * @description Mass edit multiple posts
         *
         * @tags Posts
         * @name MassEditPosts
         * @summary Mass edit posts
         * @request POST:/posts/mass-edit
         * @secure
         */
        massEditPosts: (data: any, params: RequestParams = {}) =>
            this.request<string[], void>({
                path: `/posts/mass-edit`,
                method: 'POST',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),

        /**
         * @description Create a transport post
         *
         * @tags Posts
         * @name StoreTransportPost
         * @summary Create transport post
         * @request POST:/posts/transport
         * @secure
         */
        storeTransportPost: (data: any, params: RequestParams = {}) =>
            this.request<TransportPost, void>({
                path: `/posts/transport`,
                method: 'POST',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),

        /**
         * @description Create a text post
         *
         * @tags Posts
         * @name StoreTextPost
         * @summary Create text post
         * @request POST:/posts/text
         * @secure
         */
        storeTextPost: (data: any, params: RequestParams = {}) =>
            this.request<BasePost, void>({
                path: `/posts/text`,
                method: 'POST',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),

        /**
         * @description Create a location post
         *
         * @tags Posts
         * @name StoreLocationPost
         * @summary Create location post
         * @request POST:/posts/location
         * @secure
         */
        storeLocationPost: (data: any, params: RequestParams = {}) =>
            this.request<LocationPost, void>({
                path: `/posts/location`,
                method: 'POST',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),

        /**
         * @description Update transport-specific fields of a post
         *
         * @tags Posts, TransportPosts
         * @name UpdateTransportPostExit
         * @summary Update transport post
         * @request PUT:/posts/{id}/transport/exit
         * @secure
         */
        updateTransportPostExit: (
            id: string,
            data: TransportPostExitUpdateRequest,
            params: RequestParams = {},
        ) =>
            this.request<TransportPost, void>({
                path: `/posts/${id}/transport/exit`,
                method: 'PUT',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),

        /**
         * @description Update transport times for a transport post
         *
         * @tags Posts, TransportPosts
         * @name UpdateTransportTimes
         * @summary Update transport times
         * @request PUT:/posts/{id}/transport/times
         * @secure
         */
        updateTransportTimes: (
            id: string,
            data: TransportTimesUpdateRequest,
            params: RequestParams = {},
        ) =>
            this.request<TransportPost, void>({
                path: `/posts/${id}/transport/times`,
                method: 'PUT',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),
    };
    location = {
        /**
         * @description Prefetch location data and optionally store user history
         *
         * @tags Location
         * @name PrefetchLocation
         * @summary Prefetch location
         * @request POST:/location/prefetch
         */
        prefetchLocation: (
            query: {
                /** @format float */
                latitude: number;
                /** @format float */
                longitude: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<void, any>({
                path: `/location/prefetch`,
                method: 'POST',
                query: query,
                ...params,
            }),

        /**
         * @description Return a recent location matching the requested coordinates
         *
         * @tags Location
         * @name GetRecentRequestLocation
         * @summary Get recent request location
         * @request GET:/location/request-location
         */
        getRecentRequestLocation: (
            query: {
                /** @format float */
                latitude: number;
                /** @format float */
                longitude: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<any, any>({
                path: `/location/request-location`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),
    };
    locations = {
        /**
         * @description Search for locations near a point or by query
         *
         * @tags Location
         * @name SearchLocations
         * @summary Search locations
         * @request GET:/locations/nearby
         */
        searchLocations: (
            query: {
                /** @format float */
                latitude: number;
                /** @format float */
                longitude: number;
                query?: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<LocationDto[], any>({
                path: `/locations/nearby`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),

        /**
         * @description Return location history for the authenticated user for a given day
         *
         * @tags Location
         * @name LocationHistory
         * @summary Location history
         * @request GET:/locations/history
         */
        locationHistory: (
            query?: {
                /** @format date */
                when?: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<LocationHistoryDto, any>({
                path: `/locations/history`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),

        /**
         * @description Return departures for a given point or station identifier
         *
         * @tags Location
         * @name Departures
         * @summary Departures
         * @request GET:/locations/departures
         */
        departures: (
            query?: {
                /** @format float */
                latitude?: number;
                /** @format float */
                longitude?: number;
                identifier?: string;
                /** @format date-time */
                when?: string;
                modes?: TransportMode[];
            },
            params: RequestParams = {},
        ) =>
            this.request<DeparturesResponseDto, any>({
                path: `/locations/departures`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),

        /**
         * @description Return stopovers for a given trip start
         *
         * @tags Location
         * @name Stopovers
         * @summary Stopovers
         * @request GET:/locations/stopovers
         */
        stopovers: (
            query: {
                tripId: string;
                startId: string;
                startTime: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<StopoversResponseDto, any>({
                path: `/locations/stopovers`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),
    };
    geocode = {
        /**
         * @description Geocode a query using configured providers
         *
         * @tags Location
         * @name Geocode
         * @summary Geocode
         * @request GET:/geocode
         */
        geocode: (
            query: {
                query: string;
                provider?: string;
                /** @format float */
                latitude?: number;
                /** @format float */
                longitude?: number;
            },
            params: RequestParams = {},
        ) =>
            this.request<MotisGeocodeResponseEntry[], any>({
                path: `/geocode`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),
    };
    map = {
        /**
         * @description Get a LineString geometry between two locations
         *
         * @tags Map
         * @name GetLineStringBetween
         * @summary Get linestring
         * @request GET:/map/linestring
         */
        getLineStringBetween: (
            query: {
                from: string;
                to: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<object, any>({
                path: `/map/linestring`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),

        /**
         * @description Get stop points between two locations as MultiPoint geometry
         *
         * @tags Map
         * @name GetStopsBetween
         * @summary Get stopovers
         * @request GET:/map/stopovers
         */
        getStopsBetween: (
            query: {
                from: string;
                to: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<object, any>({
                path: `/map/stopovers`,
                method: 'GET',
                query: query,
                format: 'json',
                ...params,
            }),
    };
    notifications = {
        /**
         * @description List notifications for authenticated user
         *
         * @tags Notifications
         * @name ListNotifications
         * @summary List notifications
         * @request GET:/notifications/list
         */
        listNotifications: (params: RequestParams = {}) =>
            this.request<void, any>({
                path: `/notifications/list`,
                method: 'GET',
                ...params,
            }),

        /**
         * @description Get unread notifications count
         *
         * @tags Notifications
         * @name UnreadNotificationCount
         * @summary Unread count
         * @request GET:/notifications/unread-count
         */
        unreadNotificationCount: (params: RequestParams = {}) =>
            this.request<
                {
                    /** Number of unread notifications */
                    count: number;
                },
                any
            >({
                path: `/notifications/unread-count`,
                method: 'GET',
                format: 'json',
                ...params,
            }),

        /**
         * @description Mark a notification as read
         *
         * @tags Notifications
         * @name MarkNotificationAsRead
         * @summary Mark as read
         * @request POST:/notifications/{id}/read
         */
        markNotificationAsRead: (id: string, params: RequestParams = {}) =>
            this.request<void, any>({
                path: `/notifications/${id}/read`,
                method: 'POST',
                ...params,
            }),

        /**
         * @description Mark all notifications as read
         *
         * @tags Notifications
         * @name MarkAllNotificationsAsRead
         * @summary Mark all as read
         * @request POST:/notifications/read-all
         */
        markAllNotificationsAsRead: (params: RequestParams = {}) =>
            this.request<void, any>({
                path: `/notifications/read-all`,
                method: 'POST',
                ...params,
            }),
    };
    timeline = {
        /**
         * @description Returns paginated posts for the authenticated user timeline
         *
         * @tags Posts
         * @name Timeline
         * @summary Get timeline posts
         * @request GET:/timeline
         * @secure
         */
        timeline: (
            query?: {
                /** Pagination cursor */
                cursor?: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<PostPaginationDto, void>({
                path: `/timeline`,
                method: 'GET',
                query: query,
                secure: true,
                format: 'json',
                ...params,
            }),
    };
    users = {
        /**
         * @description Returns paginated posts for a specific user
         *
         * @tags Posts
         * @name PostsForUser
         * @summary Get posts for a specific user
         * @request GET:/users/{userId}/posts
         * @secure
         */
        postsForUser: (
            userId: string,
            query?: {
                /** Pagination cursor */
                cursor?: string;
            },
            params: RequestParams = {},
        ) =>
            this.request<PostPaginationDto, void>({
                path: `/users/${userId}/posts`,
                method: 'GET',
                query: query,
                secure: true,
                format: 'json',
                ...params,
            }),

        /**
         * @description Return GeoJSON map data for a user
         *
         * @tags Profile
         * @name GetProfileMapData
         * @summary Profile map data
         * @request GET:/users/{userId}/map-data
         */
        getProfileMapData: (userId: string, params: RequestParams = {}) =>
            this.request<object, any>({
                path: `/users/${userId}/map-data`,
                method: 'GET',
                format: 'json',
                ...params,
            }),
    };
    trips = {
        /**
         * @description Create a new trip
         *
         * @tags Trips
         * @name StoreTrip
         * @summary Store trip
         * @request POST:/trips
         * @secure
         */
        storeTrip: (data: StoreTripRequest, params: RequestParams = {}) =>
            this.request<TripCreationResponseDto, void>({
                path: `/trips`,
                method: 'POST',
                body: data,
                secure: true,
                type: ContentType.Json,
                format: 'json',
                ...params,
            }),
    };
    profile = {
        /**
         * @description Return profile data for a user
         *
         * @tags Profile
         * @name GetProfile
         * @summary Get profile
         * @request GET:/profile/{username}
         */
        getProfile: (username: string, params: RequestParams = {}) =>
            this.request<UserDto, any>({
                path: `/profile/${username}`,
                method: 'GET',
                format: 'json',
                ...params,
            }),
    };
}
