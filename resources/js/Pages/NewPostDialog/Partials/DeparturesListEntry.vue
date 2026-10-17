<script setup lang="ts">
import TimeDisplay from '@/Pages/NewPostDialog/Partials/TimeDisplay.vue';
import { getColor, getEmoji } from '@/Services/DepartureTypeService';
import {
    getDepartureLineName,
    getDepartureTripNumber,
} from '@/Services/LineNameFormattingService';
import MotisTimeService from '@/Services/MotisTimeService';
import { defineProps, PropType, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
import { StopDto, StopTimeDto } from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    stopTime: {
        type: Object as PropType<StopTimeDto>,
        default: () => ({}) as StopTimeDto,
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

function getRouteTextColor(stopTime: StopTimeDto) {
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

function getRouteColor(stopTime: StopTimeDto) {
    if (stopTime.routeColor) {
        return '#' + stopTime.routeColor;
    }

    return getColor(stopTime.mode);
}
</script>

<template>
    <RouterLink
        v-slot="{ navigate }"
        :to="{
            path: '/posts/transport/stopovers',
            query: {
                tripId: stopTime.tripId,
                startId: stopTime.place.stopId,
                startTime: timeService.plannedTime?.toISO() || undefined,
            },
        }"
    >
        <li
            class="list-row hover:bg-base-200 cursor-pointer grid-cols-13 items-center gap-0 py-2 md:px-8"
            @click="navigate"
        >
            <div class="col col-span-2 text-left md:col-span-1">
                <TimeDisplay
                    :planned-time="plannedTime"
                    :time="time"
                    :delay="delay"
                    :real-time="props.stopTime.realTime"
                    :cancelled="!!props.stopTime.place.cancelled"
                />
            </div>
            <div class="col col-span-9 md:col-span-10">
                <div
                    :class="{
                        'text-error line-through': stopTime.place.cancelled,
                    }"
                >
                    <div class="flex flex-row">
                        <div class="badge badge-transparent badge-xs">
                            {{ emoji }}
                        </div>
                        <div
                            class="badge badge-xs min-w-[3em] px-[0.5] whitespace-nowrap"
                            :style="`background-color: ${getRouteColor(stopTime)}; color: ${getRouteTextColor(stopTime)}`"
                        >
                            {{ getDepartureLineName(stopTime) }}
                        </div>
                        <div
                            class="badge badge-transparent badge-xs text-xs font-medium opacity-65"
                        >
                            {{ getDepartureTripNumber(stopTime) }}
                        </div>
                        <span
                            v-if="stopTime.place.cancelled"
                            class="badge badge-error badge-xs ml-1 no-underline"
                        >
                            {{ t('common.cancelled') }}
                        </span>
                    </div>
                    <span class="line-clamp-1">
                        {{ stopTime.headSign }}
                    </span>
                </div>
                <div
                    v-if="stopTime.place.name !== stop.name"
                    class="line-clamp-1 overflow-x-hidden text-xs opacity-60"
                >
                    {{
                        t('common.departs_from', {
                            location: stopTime.place.name,
                        })
                    }}
                </div>
            </div>
            <div class="col col-span-2 text-right">
                <div
                    v-if="
                        stopTime.place.track !== stopTime.place.scheduledTrack
                    "
                    class="text-error line-through"
                >
                    {{
                        t('common.platform_short', {
                            platform: stopTime.place.scheduledTrack,
                        })
                    }}
                </div>
                <div v-if="stopTime.place.track">
                    {{
                        t('common.platform_short', {
                            platform: stopTime.place.track,
                        })
                    }}
                </div>
            </div>
        </li>
    </RouterLink>
</template>

<style scoped>
.badge {
    height: auto;
}
.badge-transparent {
    background-color: transparent;
    color: inherit;
    border-width: 0;
}
</style>
