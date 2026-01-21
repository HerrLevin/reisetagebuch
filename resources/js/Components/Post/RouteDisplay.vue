<script setup lang="ts">
import Delay from '@/Components/Post/Partials/Delay.vue';
import {
    getPostLineName,
    getPostTripNumber,
} from '@/Services/ApiLineNameFormattingService';
import { getColor, getEmoji } from '@/Services/DepartureTypeService';
import {
    formatArrivalTime,
    formatDepartureTime,
} from '@/Services/TimeFormattingService';
import { getArrivalDelay, getDepartureDelay } from '@/Services/TripTimeService';
import { LocationEntry } from '@/types';
import { DateTime } from 'luxon';
import { computed, PropType, ref, watch } from 'vue';
import { TransportPost, TripDto } from '../../../types/Api.gen';

const props = defineProps({
    post: {
        type: Object as PropType<TransportPost>,
        required: true,
    },
});

// Local reactive post state
const localPost = ref(props.post);

// Watch for prop changes and update localPost
watch(
    () => props.post,
    (newPost) => {
        localPost.value = newPost;
    },
);

const departureDelay = computed(() => {
    return getDepartureDelay(localPost.value);
});
const arrivalDelay = computed(() => {
    return getArrivalDelay(localPost.value);
});

function getFormattedDepartureTime(): string | null {
    return formatDepartureTime(
        localPost.value?.originStop,
        localPost.value?.manualDepartureTime,
        departureDelay.value || 0,
    );
}

function getFormattedArrivalTime(): string | null {
    return formatArrivalTime(
        localPost.value?.destinationStop,
        localPost.value?.manualArrivalTime,
        arrivalDelay.value || 0,
    );
}

function getRouteTextColor(trip: TripDto) {
    if (trip.routeTextColor && trip.routeTextColor.length > 2) {
        return '#' + trip.routeTextColor;
    }

    return '#FFFFFF';
}

function getRouteColor(trip: TripDto) {
    if (trip.routeColor && trip.routeColor.length > 2) {
        return '#' + trip.routeColor;
    }

    return getColor(trip.mode);
}

function selectStation(location: LocationEntry) {
    const identifier = location.identifiers.find((id) => id.origin === 'motis');

    window.location.href = route('posts.create.departures', {
        latitude: location.latitude,
        longitude: location.longitude,
        identifier: identifier?.identifier || '',
        when: DateTime.now().toISO(),
    });
}
</script>

<template>
    <div>
        <div class="grid grid-cols-2 gap-0 pb-0">
            <div class="text-left">
                <div
                    class="mb-2 line-clamp-2 leading-none font-semibold overflow-ellipsis"
                    @click.prevent="
                        selectStation(localPost.originStop.location)
                    "
                >
                    {{ localPost.originStop.location.name }}
                </div>
            </div>
            <div class="text-right">
                <div
                    class="mb-2 line-clamp-2 leading-none font-semibold overflow-ellipsis"
                    @click.prevent="
                        selectStation(localPost.destinationStop.location)
                    "
                >
                    {{ localPost.destinationStop.location.name }}
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
                    v-show="localPost.trip.lineName"
                    class="badge min-w-[3em] px-[0.5] text-sm font-medium"
                    :style="`background-color: ${getRouteColor(localPost.trip)}; color: ${getRouteTextColor(localPost.trip)}`"
                >
                    {{ getPostLineName(localPost) }}
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
            <div class="divider divider-dashed mt-0 mb-0">
                <div>{{ getEmoji(localPost.trip.mode!) }}</div>
            </div>
        </div>
        <div class="flex w-full flex-col text-center text-xs font-medium">
            {{ getPostTripNumber(localPost) }}
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
