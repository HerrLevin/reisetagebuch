<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StopoversListEntry from '@/Pages/NewPostDialog/Partials/StopoversListEntry.vue';
import { getEmoji } from '@/Services/DepartureTypeService';
import { StopPlace, TripDto } from '@/types';
import { Head } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { PropType, ref } from 'vue';

const props = defineProps({
    trip: {
        type: Object as PropType<TripDto> | null,
        required: false,
    },
    startTime: {
        type: String,
        default: '',
    },
    tripId: {
        type: String,
        default: '',
    },
    startId: {
        type: String,
        default: '',
    },
});
const stopovers = ref([] as StopPlace[]);
const filterTime = props.startTime ? DateTime.fromISO(props.startTime) : null;

stopovers.value = props.trip?.legs[0]
    ? [...props.trip!.legs[0].intermediateStops, props.trip!.legs[0].to]
    : [];

stopovers.value = stopovers.value.filter((stopover) => {
    const time = stopover.scheduledDeparture || stopover.scheduledArrival;
    const luxonTime = time ? DateTime.fromISO(time) : null;

    if (filterTime && luxonTime) {
        return luxonTime >= filterTime;
    }
});

function redirectCreatePost(stopover: StopPlace) {
    const params = {
        tripId: props.tripId,
        startId: props.startId,
        startTime: props.startTime,
        stopId: stopover.stopId,
        stopTime: stopover.scheduledDeparture || stopover.scheduledArrival,
        stopName: stopover.name,
        stopMode: props.trip?.legs[0].mode,
        lineName: props.trip?.legs[0].routeShortName,
    };
    window.location.href = route('posts.create.transport-post', params);
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Post</h2>
        </template>
        <div class="card bg-base-100 min-w-full shadow-md">
            <!-- Results -->
            <ul class="list">
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    {{ trip?.legs[0].mode ? getEmoji(trip?.legs[0].mode) : '' }}
                    {{ trip?.legs[0].routeShortName }}
                    |
                    {{ trip?.legs[0].headSign }}
                </li>
                <StopoversListEntry
                    v-for="(stopover, index) in stopovers"
                    :key="index"
                    :stop="stopover"
                    :mode="trip?.legs[0].mode"
                    :shortName="trip?.legs[0].routeShortName"
                    :realTime="trip?.legs[0].realTime"
                    @click="redirectCreatePost(stopover)"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
