<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LocationListEntry from '@/Pages/NewPostDialog/Partials/LocationListEntry.vue';
import { LocationService } from '@/Services/LocationService';
import { LocationEntry, RequestLocationDto } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    locations: {
        type: Array<LocationEntry>,
        default: () => [] as LocationEntry[],
        required: false,
    },
});

const showStartButton = ref(false);
showStartButton.value = route().current('posts.create.start');

const filteredLocations = ref<LocationEntry[]>([]);
const search = ref<string>('');
const fetchingProgress = ref<RequestLocationDto | null>(null);

function fetchRequestLocation() {
    LocationService.getPosition().then((position) => {
        fetch(
            route('api.request-location.get', {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            }),
        )
            .then((response: Response) => {
                if (response.ok) {
                    response.json().then((data: RequestLocationDto) => {
                        fetchingProgress.value = data;
                    });
                } else {
                    console.error(
                        'Failed to fetch request location:',
                        response.statusText,
                    );
                }
            })
            .catch((error) => {
                console.error('Error fetching request location:', error);
            });
    });
}

function filterLocations() {
    if (search.value.length <= 0) {
        filteredLocations.value = props.locations;
    } else {
        filteredLocations.value = props.locations.filter((location) =>
            location.name.toLowerCase().includes(search.value.toLowerCase()),
        );
    }
}
filterLocations();
fetchRequestLocation();

// fetch every 5 seconds
const fetchInterval = setInterval(() => {
    fetchRequestLocation();
}, 5000);

// watch for fetchingProgress changes
watch(fetchingProgress, (newValue) => {
    if (newValue) {
        const progress = (newValue.fetched / newValue.toFetch) * 100;
        if (progress >= 100) {
            clearInterval(fetchInterval);
            fetchingProgress.value = null; // Reset after completion
        }
    }
});
</script>

<template>
    <Head title="Locations nearby" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Post</h2>
        </template>
        <div class="card bg-base-100 min-w-full shadow-md">
            <!-- Search Bar -->
            <div class="w-full p-8">
                <label class="input w-full">
                    <svg
                        class="h-[1em] opacity-50"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                    >
                        <g
                            stroke-linejoin="round"
                            stroke-linecap="round"
                            stroke-width="2.5"
                            fill="none"
                            stroke="currentColor"
                        >
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </g>
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        class="grow"
                        placeholder="Search"
                        autofocus
                        @keyup="filterLocations"
                    />
                    <!--                    <kbd class="kbd kbd-sm">⌘</kbd>-->
                    <!--                    <kbd class="kbd kbd-sm">K</kbd>-->
                </label>
            </div>

            <!-- Results -->
            <ul class="list">
                <li
                    v-if="
                        fetchingProgress &&
                        fetchingProgress.fetched < fetchingProgress.toFetch
                    "
                    class="p-4 pb-2 text-xs tracking-wide text-green-500"
                >
                    <div
                        class="radial-progress"
                        :style="`
                            --value: ${
                                (fetchingProgress.fetched /
                                    fetchingProgress.toFetch) *
                                100
                            };
                            --size: 1.5rem;
                            --thickness: 0.25rem;
                        `"
                        aria-valuenow="0"
                        role="progressbar"
                    ></div>
                    Fetching locations from OSM...
                </li>
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    Locations nearby
                </li>

                <LocationListEntry
                    v-for="location in filteredLocations"
                    :key="location.id"
                    :data="{ location: location }"
                    :location
                    :show-start-button
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
