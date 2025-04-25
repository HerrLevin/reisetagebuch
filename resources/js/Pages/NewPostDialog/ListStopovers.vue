<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { StopPlace, TripDto } from '@/types';
import { Head } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import StopoversListEntry from '@/Pages/NewPostDialog/Partials/StopoversListEntry.vue';
import { getEmoji } from '../../Services/DepartureTypeService';

const props = defineProps({
    trip: {
        type: Object as PropType<TripDto> | null,
        required: false,
    },
});
const stopovers = ref([] as StopPlace[]);

stopovers.value = props.trip?.legs[0]
    ? [
          props.trip!.legs[0].from,
          ...props.trip!.legs[0].intermediateStops,
          props.trip!.legs[0].to,
      ]
    : [];
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
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
