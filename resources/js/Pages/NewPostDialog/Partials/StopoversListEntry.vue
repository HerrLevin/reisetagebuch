<script setup lang="ts">
import TimeDisplay from '@/Pages/NewPostDialog/Partials/TimeDisplay.vue';
import MotisTimeService from '@/Services/MotisTimeService';
import { defineProps, onMounted, PropType, ref } from 'vue';
import { StopPlaceDto } from '../../../../types/Api.gen';

const props = defineProps({
    stop: {
        type: Object as PropType<StopPlaceDto>,
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
    selected: {
        type: Boolean,
        default: false,
    },
});

const timeService = new MotisTimeService(props.stop);
const plannedTime = ref(timeService.plannedTimeString);
const time = ref(timeService.timeString);
const delay = ref(timeService.delay);

const row = ref<HTMLElement | null>(null);

onMounted(() => {
    if (props.selected) {
        row.value?.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<template>
    <li
        ref="row"
        class="list-row hover:bg-base-200 cursor-pointer grid-cols-8"
        :class="{ 'bg-base-300': selected }"
    >
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
