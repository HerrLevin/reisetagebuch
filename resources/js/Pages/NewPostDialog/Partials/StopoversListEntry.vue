<script setup lang="ts">
import { getEmoji } from '@/Services/DepartureTypeService';
import { StopPlace } from '@/types';
import { TransportMode } from '@/types/enums';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
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
});

const emoji = ref('');
const time = ref('');
const plannedTime = ref('');
const delay = ref(-1);

const _scheduledTime =
    props.stop.scheduledDeparture ?? props.stop.scheduledArrival;
const luxonSchedule = _scheduledTime ? DateTime.fromISO(_scheduledTime) : null;
const _time = props.stop.departure ?? props.stop.arrival;
const luxonTime = _time ? DateTime.fromISO(_time) : null;

delay.value =
    luxonTime && luxonSchedule
        ? luxonTime.diff(luxonSchedule, 'minutes').minutes
        : -1;

time.value = formatTime(luxonTime ?? luxonSchedule);
plannedTime.value = formatTime(luxonSchedule);

emoji.value = props.mode ? getEmoji(props.mode as TransportMode) : '';

function formatTime(date: DateTime | null): string {
    if (!date) {
        return '';
    }
    return date.toFormat('HH:mm');
}

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

<style scoped>
.badge {
    height: auto;
}
</style>
