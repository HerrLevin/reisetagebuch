<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeparturesFilter from '@/Pages/NewPostDialog/Partials/DeparturesFilter.vue';
import DeparturesListEntry from '@/Pages/NewPostDialog/Partials/DeparturesListEntry.vue';
import { LocationService } from '@/Services/LocationService';
import { DeparturesDto } from '@/types';
import { TransportMode } from '@/types/enums';
import { Head } from '@inertiajs/vue3';
import { onMounted, PropType, ref } from 'vue';

defineProps({
    departures: {
        type: Object as PropType<DeparturesDto> | null,
        required: false,
    },
    filter: {
        type: Array as PropType<TransportMode[]>,
        default: () => [],
    },
    requestTime: {
        type: String,
        default: '',
    },
});

const latitude = ref(0);
const longitude = ref(0);
onMounted(() => {
    LocationService.getPosition().then((position) => {
        latitude.value = position.coords.latitude;
        longitude.value = position.coords.longitude;
    });
});

const showStartButton = ref(false);
showStartButton.value = route().current('posts.create.start');
</script>

<template>
    <Head :title="`${departures?.stop.name}: departures`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Post</h2>
        </template>
        <DeparturesFilter
            :latitude="latitude"
            :longitude="longitude"
            :filter="filter"
            :requestTime="requestTime"
        />
        <div class="card bg-base-100 min-w-full shadow-md">
            <!-- Results -->
            <ul class="list">
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    Departures at {{ departures?.stop.name }}
                </li>
                <DeparturesListEntry
                    v-for="(departure, index) in departures?.departures"
                    :key="index"
                    :stopTime="departure"
                    :showStartButton="showStartButton"
                    :stop="departures!.stop"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
