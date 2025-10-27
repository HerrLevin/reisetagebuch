<script setup lang="ts">
import Delay from '@/Components/Post/Partials/Delay.vue';
import { getColor, getEmoji } from '@/Services/DepartureTypeService';
import {
    formatArrivalTime,
    formatDepartureTime,
} from '@/Services/TimeFormattingService';
import { getArrivalDelay, getDepartureDelay } from '@/Services/TripTimeService';
import { TransportPost, Trip } from '@/types/PostTypes';
import type { PropType } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<TransportPost>,
        required: true,
    },
});

const departureDelay = getDepartureDelay(props.post);
const arrivalDelay = getArrivalDelay(props.post);

function getFormattedDepartureTime(): string | null {
    return formatDepartureTime(
        props.post?.originStop,
        props.post?.manualDepartureTime,
        departureDelay || 0,
    );
}

function getFormattedArrivalTime(): string | null {
    return formatArrivalTime(
        props.post?.destinationStop,
        props.post?.manualArrivalTime,
        arrivalDelay || 0,
    );
}

function getRouteTextColor(trip: Trip) {
    if (trip.routeTextColor && trip.routeTextColor.length > 2) {
        return '#' + trip.routeTextColor;
    }

    return '#FFFFFF';
}

function getRouteColor(trip: Trip) {
    if (trip.routeColor && trip.routeColor.length > 2) {
        return '#' + trip.routeColor;
    }

    return getColor(trip.mode);
}
</script>

<template>
    <div>
        <div class="grid grid-cols-2 gap-0 pb-0">
            <div class="text-left">
                <div
                    class="mb-2 line-clamp-2 leading-none font-semibold overflow-ellipsis"
                >
                    {{ post.originStop.location.name }}
                </div>
            </div>
            <div class="text-right">
                <div
                    class="mb-2 line-clamp-2 leading-none font-semibold overflow-ellipsis"
                >
                    {{ post.destinationStop.location.name }}
                </div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-0 pb-1">
            <div class="text-left">
                <p class="text-muted-foreground text-sm font-medium">
                    {{ getFormattedDepartureTime() }}
                </p>
                <Delay :delay="departureDelay" />
            </div>
            <div class="self-end text-center">
                <div
                    v-show="post.trip.lineName"
                    class="badge min-w-[3em] text-sm font-medium"
                    :style="`background-color: ${getRouteColor(post.trip)}; color: ${getRouteTextColor(post.trip)}`"
                >
                    {{ post.trip.displayName || post.trip.lineName }}
                </div>
            </div>
            <div class="text-right">
                <p class="text-muted-foreground text-sm font-medium">
                    {{ getFormattedArrivalTime() }}
                </p>
                <Delay :delay="arrivalDelay" />
            </div>
        </div>
        <div class="flex w-full flex-col">
            <div class="divider divider-dashed mt-0">
                {{ getEmoji(post.trip.mode!) }}
            </div>
        </div>
    </div>
</template>

<style scoped>
.divider-dashed {
    &::before,
    &::after {
        background: repeating-linear-gradient(
            90deg,
            transparent,
            transparent 5px,
            color-mix(in oklab, var(--color-base-content) 10%, transparent) 5px,
            color-mix(in oklab, var(--color-base-content) 10%, transparent) 10px
        );
    }
}
</style>
