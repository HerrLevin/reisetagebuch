<script setup lang="ts">
import TimeDisplay from '@/Pages/NewPostDialog/Partials/TimeDisplay.vue';
import MotisTimeService from '@/Services/MotisTimeService';
import { StopPlace } from '@/types';
import { defineProps, PropType, ref } from 'vue';

const props = defineProps({
    stop: {
        type: Object as PropType<StopPlace>,
        default: () => ({}),
    },
    mode: {
        type: String,
        default: '',
    },
    shortName: {
        type: String,
        default: '',
    },
    realTime: {
        type: Boolean,
        default: false,
    },
});

const timeService = new MotisTimeService(props.stop);
const plannedTime = ref(timeService.plannedTimeString);
const time = ref(timeService.timeString);
const delay = ref(timeService.delay);
</script>

<template>
    <li class="list-row hover:bg-base-200 cursor-pointer grid-cols-8">
        <div class="col col-span-6">
            {{ stop.name }}
        </div>
        <div class="col col-span-2 text-right">
            <TimeDisplay
                :planned-time="plannedTime"
                :time="time"
                :delay="delay"
                :real-time="realTime"
            />
        </div>
    </li>
</template>

<style scoped>
.badge {
    height: auto;
}
</style>
