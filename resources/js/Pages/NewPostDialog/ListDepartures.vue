<script setup lang="ts">
import XMark from '@/Icons/XMark.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeparturesListEntry from '@/Pages/NewPostDialog/Partials/DeparturesListEntry.vue';
import {
    FilterGroups,
    getColor,
    getEmoji,
} from '@/Services/DepartureTypeService';
import { LocationService } from '@/Services/LocationService';
import { DeparturesDto } from '@/types';
import { TransportMode } from '@/types/enums';
import { Head, Link } from '@inertiajs/vue3';
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
        <!-- Filters -->
        <div class="card bg-base-100 mb-4 min-w-full shadow-md">
            <ul class="list">
                <li
                    class="flex gap-1 overflow-x-scroll p-4 pb-2 text-xs tracking-wide"
                >
                    <Link
                        v-if="filter.length > 0"
                        class="btn btn-sm btn-neutral"
                        :href="
                            route('posts.create.departures', {
                                latitude: latitude,
                                longitude: longitude,
                            })
                        "
                    >
                        <XMark class="h-4 w-4" />
                    </Link>

                    <Link
                        class="btn btn-sm text-white"
                        :class="{
                            'opacity-60':
                                filter.length > 0 && mode[0] !== filter[0],
                        }"
                        :href="
                            route('posts.create.departures', {
                                latitude: latitude,
                                longitude: longitude,
                                filter: mode.join(','),
                            })
                        "
                        :style="`background-color: ${getColor(mode[0])}`"
                        v-for="(mode, name) in FilterGroups"
                        :key="name"
                    >
                        {{ getEmoji(mode[0]) }}

                        {{ name }}
                    </Link>
                </li>
            </ul>
        </div>
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
