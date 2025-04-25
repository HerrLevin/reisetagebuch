<script setup lang="ts">
import TimeDisplay from '@/Pages/NewPostDialog/Partials/TimeDisplay.vue';
import { getColor, getEmoji } from '@/Services/DepartureTypeService';
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
</script>

<template>
    <Link
        :href="route('posts.create.stopovers')"
        :data="linkData"
        as="li"
        class="list-row hover:bg-base-200 cursor-pointer grid-cols-11"
    >
        <div class="col text-center text-3xl">
            {{ emoji }}
        </div>
        <div class="col col-span-2 text-center">
            <div
                class="badge min-w-[3em] text-white"
                :style="`background-color: ${getColor(stopTime.mode)}`"
            >
                {{ stopTime.routeShortName }}
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
                :planned="plannedTime"
                :time="time"
                :delay="delay"
                :realTime="props.stopTime.realTime"
            />
        </div>
    </Link>
</template>

<style scoped>
.badge {
    height: auto;
}
</style>
