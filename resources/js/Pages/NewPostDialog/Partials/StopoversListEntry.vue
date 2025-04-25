<script setup lang="ts">
import { getEmoji } from '@/Services/DepartureTypeService';
import { StopPlace } from '@/types';
import { TransportMode } from '@/types/enums';
import { Link } from '@inertiajs/vue3';
import { defineProps, PropType, ref } from 'vue';
import TimeDisplay from '@/Pages/NewPostDialog/Partials/TimeDisplay.vue';
import MotisTimeService from '@/Services/MotisTimeService';

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

const emoji = ref('');
const timeService = new MotisTimeService(props.stop);
const plannedTime = ref(timeService.plannedTimeString);
const time = ref(timeService.timeString);
const delay = ref(timeService.delay);

emoji.value = props.mode ? getEmoji(props.mode as TransportMode) : '';

const linkData = ref({
    location: {
        emoji: emoji.value,
        name: props.shortName + ' ➜ ' + props.stop.name,
        id: '',
    },
});
</script>

<template>
    <Link
        :href="route('posts.create.post')"
        :data="linkData"
        as="li"
        class="list-row hover:bg-base-200 cursor-pointer grid-cols-8"
    >
        <div class="col col-span-6">
            {{ stop.name }}
        </div>
        <div class="col col-span-2 text-right">
            <TimeDisplay
                :planned="plannedTime"
                :time="time"
                :delay="delay"
                :realTime="realTime"
            />
        </div>
    </Link>
</template>

<style scoped>
.badge {
    height: auto;
}
</style>
