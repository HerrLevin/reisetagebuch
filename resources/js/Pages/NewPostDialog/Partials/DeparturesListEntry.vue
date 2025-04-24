<script setup lang="ts">
import { getColor, getEmoji } from '@/Services/DepartureTypeService';
import { StopDto, StopTime } from '@/types';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
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
    data: {
        type: Object,
        default: () => ({}),
    },
    stop: {
        type: Object as PropType<StopDto>,
        default: () => ({}),
    },
});

const emoji = ref('');
const time = ref('');
const plannedTime = ref('');
const delay = ref(-1);

emoji.value = getEmoji(props.stopTime.mode);

const linkData = ref({
    location: {
        emoji: emoji.value,
        name: props.stopTime.headSign,
        id: props.stopTime.tripId,
    },
});

const _scheduledTime =
    props.stopTime.place.scheduledDeparture ??
    props.stopTime.place.scheduledArrival;
const luxonSchedule = _scheduledTime ? DateTime.fromISO(_scheduledTime) : null;
const _time = props.stopTime.place.departure ?? props.stopTime.place.arrival;
const luxonTime = _time ? DateTime.fromISO(_time) : null;
const realTime = props.stopTime.realTime;

delay.value =
    realTime && luxonTime && luxonSchedule
        ? luxonTime.diff(luxonSchedule, 'minutes').minutes
        : -1;

time.value = realTime
    ? formatTime(luxonTime ?? luxonSchedule)
    : formatTime(luxonSchedule);
plannedTime.value = realTime ? formatTime(luxonSchedule) : '';

function formatTime(date: DateTime | null): string {
    if (!date) {
        return '';
    }
    return date.toFormat('HH:mm');
}
</script>

<template>
    <Link
        :href="route('posts.create.post')"
        :data="linkData"
        as="li"
        class="list-row hover:bg-base-200 cursor-pointer"
    >
        <div class="flex min-w-[5rem] items-center justify-between text-3xl">
            {{ emoji }}

            <div
                class="badge min-w-[3em]"
                :style="`background-color: ${getColor(stopTime.mode)}`"
            >
                {{ stopTime.routeShortName }}
            </div>
        </div>
        <div>
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
        <div>
            <div
                :class="{
                    'text-warning': delay < 4 && delay >= 2,
                    'text-success': delay < 2 && delay >= 0,
                    'text-error': delay >= 4,
                }"
            >
                {{ time }}
            </div>
            <div v-if="delay" class="line-through opacity-60">
                {{ plannedTime }}
            </div>
        </div>
    </Link>
</template>

<style scoped></style>
