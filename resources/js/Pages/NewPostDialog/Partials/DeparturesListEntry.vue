<script setup lang="ts">
import TimeDisplay from '@/Pages/NewPostDialog/Partials/TimeDisplay.vue';
import { getColor, getEmoji } from '@/Services/DepartureTypeService';
import {
    getDepartureLineName,
    getDepartureTripNumber,
} from '@/Services/LineNameFormattingService';
import MotisTimeService from '@/Services/MotisTimeService';
import { StopDto, StopTime } from '@/types';
import { Link } from '@inertiajs/vue3';
import { defineProps, PropType, ref } from 'vue';

const props = defineProps({
    stopTime: {
        type: Object as PropType<StopTime>,
        default: () => ({}) as StopTime,
    },
    showStartButton: {
        type: Boolean,
        default: true,
    },
    stop: {
        type: Object as PropType<StopDto>,
        default: () => ({}),
    },
});

const emoji = ref('');

const timeService = new MotisTimeService(props.stopTime.place);
const plannedTime = ref(timeService.plannedTimeString);
const time = ref(timeService.timeString);
const delay = ref(timeService.delay);

emoji.value = getEmoji(props.stopTime.mode);

const linkData = ref({
    tripId: props.stopTime.tripId,
    startId: props.stopTime.place.stopId,
    startTime: timeService.plannedTime?.toISO(),
});

function getRouteTextColor(stopTime: StopTime) {
    if (stopTime.routeTextColor) {
        return '#' + stopTime.routeTextColor;
    }
    const routeColor = getRouteColor(stopTime);
    if (routeColor) {
        return getContrastTextColor(routeColor);
    }

    return '#FFFFFF';
}

function getContrastTextColor(hexColor: string) {
    const color = hexColor.charAt(0) === '#' ? hexColor.substring(1) : hexColor;

    const r = parseInt(color.substring(0, 2), 16);
    const g = parseInt(color.substring(2, 4), 16);
    const b = parseInt(color.substring(4, 6), 16);

    // Calculate luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    // Return black for light colors and white for dark colors
    return luminance > 0.5 ? '#000000' : '#FFFFFF';
}

function getRouteColor(stopTime: StopTime) {
    if (stopTime.routeColor) {
        return '#' + stopTime.routeColor;
    }

    return getColor(stopTime.mode);
}
</script>

<template>
    <Link
        :href="route('posts.create.stopovers')"
        :data="linkData"
        as="li"
        class="list-row hover:bg-base-200 cursor-pointer grid-cols-11 items-center"
    >
        <div class="col text-center text-2xl">
            {{ emoji }}
        </div>
        <div class="col col-span-2 text-center">
            <div
                class="badge min-w-[3em] px-[0.5]"
                :style="`background-color: ${getRouteColor(stopTime)}; color: ${getRouteTextColor(stopTime)}`"
            >
                {{ getDepartureLineName(stopTime) }}
            </div>
            <div class="mt-0.5 text-xs font-medium">
                {{ getDepartureTripNumber(stopTime) }}
            </div>
        </div>
        <div class="col col-span-6">
            <div>
                {{ stopTime.headSign }}
            </div>
            <div
                v-if="stopTime.place.name !== stop.name"
                class="overflow-x-hidden text-xs uppercase opacity-60"
            >
                {{ stopTime.place.name }}
            </div>
        </div>
        <div class="col col-span-2 text-right">
            <TimeDisplay
                :planned-time="plannedTime"
                :time="time"
                :delay="delay"
                :real-time="props.stopTime.realTime"
            />
        </div>
    </Link>
</template>

<style scoped>
.badge {
    height: auto;
}
</style>
