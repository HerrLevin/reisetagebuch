<script setup lang="ts">
import { CircleChevronRight } from 'lucide-vue-next';
import {
    TransportMode,
    TransportPost,
    TravelReason,
    Visibility,
} from '../../../types/Api.gen';
import { formatArrivalTime } from '@/Services/TimeFormattingService';
import { getArrivalDelay } from '@/Services/TripTimeService';
import { getRouteBadgeStyle } from '@/Services/DepartureTypeService';
import { getPostLineName } from '@/Services/LineNameFormattingService';

const demo: TransportPost = {
    id: '01a00ea7-838b-73c4-b7b3-dead5ec5b101',
    user: {
        id: '0196c161-5ec7-7345-9b92-08dfbdd55004',
        name: 'Levin \ud83c\udf08',
        username: 'levin',
        avatar: 'https://reisetagebuch.herrlev.in/files/avatars/68221e7d79d73.jpeg',
        avatarMimeType: 'image/jpeg',
        header: 'https://reisetagebuch.herrlev.in/files/headers/68221ec78b950.jpeg',
        headerMimeType: 'image/jpeg',
        bio: null,
        website: null,
        isFollowed: false,
        isFollowRequested: false,
        requiresFollowRequest: true,
        statistics: {
            postsCount: 1300,
            transportPostsCount: 791,
            locationPostsCount: 509,
            followersCount: 9,
            followingCount: 8,
            travelledDistance: 21822027,
            travelledDuration: 1001462,
            visitedCountriesCount: 0,
            visitedLocationsCount: 251,
        },
        publicKeyPem:
            '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgsB0EeSq4vlzAZU/aS2m\nZEuZVzdok0l8iUpblem1/wyxvCETlcDvt5vmh1KfpeQIkLE0PNtOE/nw0PaqAchx\ngUD7pTHcCUozyQwKpR10A6Y6Sy5HFTtR5nxQDLbgwpd689t72MQculFgQgQH9NQW\nF2twxcaSg3SxywjzH8FvWSgXcdxm/titIUhRe1bEdV32xHFHvJOwmT5G5fd8bKIJ\nOLMPQ1kO/BQg1wnQzHTMnGsPh/mXfY/0lorRfP8FlJHogf17wiFPslKhSOqw9aFb\ndLvMLj3XFdEMGp+kwWVq2XgkVIy7HYd8LPOeSndTillUQj6EQ3/a3vxIk6qrIoeN\n4wIDAQAB\n-----END PUBLIC KEY-----\n',
        createdAt: '2025-05-11T22:05:06+00:00',
        profileUrl: null,
    },
    body: 'Wer will schon p\u00fcnktlich sein? Wenigstens gibt es Fahrgastrechte.',
    visibility: 'public' as Visibility,
    publishedAt: '2026-08-17T07:15:00+00:00',
    createdAt: '2026-08-17T07:37:34+00:00',
    updatedAt: '2026-08-17T13:45:04+00:00',
    hashTags: [],
    likesCount: 3,
    likedByUser: false,
    metaInfos: {
        'rtb:traewelling_trip_id': '8768170',
        'rtb:travel_reason': 'leisure',
    },
    sourceUrl: null,
    originStop: {
        id: '01a00ea7-374f-72aa-9c1f-e36f7bb7c604',
        name: 'Hamburg-Harburg',
        location: {
            id: '01979c6a-1222-70e5-9284-a95e02c99aa6',
            name: 'Hamburg-Harburg',
            latitude: 53.45591,
            longitude: 9.991700999999999,
            distance: null,
            identifiers: [
                {
                    type: 'stop',
                    origin: 'motis',
                    identifier: 'de-DELFI_de:02000:8000147',
                },
                { type: 'stop', origin: 'traewelling', identifier: '20' },
            ],
            tags: [],
            emoji: '\ud83d\udccd',
        },
        arrivalTime: '2026-08-17T07:19:00+00:00',
        departureTime: '2026-08-17T07:25:00+00:00',
        arrivalDelay: 2700,
        departureDelay: 2700,
    },
    destinationStop: {
        id: '01a00ea7-3919-707a-befa-72bfb5a766e2',
        name: 'Westerland(Sylt) ZOB/Bahnhof',
        location: {
            id: '01a00ea7-3916-71de-b149-07f2aa73fb21',
            name: 'Westerland(Sylt) ZOB/Bahnhof',
            latitude: 54.906836999999996,
            longitude: 8.310925,
            distance: null,
            identifiers: [
                {
                    type: 'stop',
                    origin: 'motis',
                    identifier: 'de-DELFI_de:01054:98523',
                },
            ],
            tags: [],
            emoji: '\ud83d\udccd',
        },
        arrivalTime: '2026-08-17T10:52:00+00:00',
        departureTime: null,
        arrivalDelay: 5160,
        departureDelay: null,
    },
    trip: {
        id: '01a00ea7-36ca-71b2-ad72-28abf26b2896',
        foreignId: '20260817_05:14_de-DELFI_3338563959',
        mode: 'HIGHSPEED_RAIL' as TransportMode,
        lineName: '33',
        routeLongName: '',
        tripShortName: 'ICE 2214',
        displayName: 'ICE 2214',
        routeColor: 'c72730',
        routeTextColor: null,
    },
    manualDepartureTime: '2026-08-17T08:13:45+00:00',
    manualArrivalTime: '2026-08-17T12:01:30+00:00',
    travelReason: 'leisure' as TravelReason,
    distance: 252959,
    duration: 13665,
    userGeometry: null,
};

function getFormattedArrivalTime(): string | null {
    return formatArrivalTime(
        demo.destinationStop,
        demo.manualArrivalTime,
        getArrivalDelay(demo) || 0,
    );
}
</script>

<template>
    <div
        onclick="my_modal_5.showModal()"
        class="btn btn-lg bg-base-300 text-base-content/65 w-full flex-col gap-0 p-2 text-start text-sm shadow-xl"
    >
        <div class="flex w-full justify-between text-xs font-normal">
            <div class="line-clamp-1 overflow-ellipsis">
                <div
                    v-show="demo.trip.lineName"
                    class="badge badge-xs"
                    :style="getRouteBadgeStyle(demo.trip)"
                >
                    {{ getPostLineName(demo) }}
                </div>
                {{ demo.destinationStop.name }}
            </div>
            <div class="ms-1">
                {{ getFormattedArrivalTime() }}
            </div>
        </div>
        <div class="flex w-full justify-between">
            <p>
                <CircleChevronRight class="inline-block size-3" />
                Itzehoe
            </p>
            <p>{{ getFormattedArrivalTime() }}</p>
        </div>
    </div>
    <dialog id="my_modal_5" class="modal modal-bottom">
        <div class="modal-box">
            <div class="flex w-full justify-between">
                <div class="line-clamp-1 font-bold overflow-ellipsis">
                    <div
                        v-show="demo.trip.lineName"
                        class="badge badge-sm"
                        :style="getRouteBadgeStyle(demo.trip)"
                    >
                        {{ getPostLineName(demo) }}
                    </div>
                    {{ demo.destinationStop.name }}
                </div>
                <form method="dialog">
                    <button
                        class="btn btn-sm btn-circle btn-ghost absolute top-4 right-2"
                    >
                        ✕
                    </button>
                </form>
            </div>

            <p class="min-h-screen py-4">
                Press ESC key or click the button below to close
            </p>
        </div>
    </dialog>
</template>
