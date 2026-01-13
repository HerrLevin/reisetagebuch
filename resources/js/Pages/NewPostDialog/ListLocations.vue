<script setup lang="ts">
import Loading from '@/Components/Loading.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LocationListEntry from '@/Pages/NewPostDialog/Partials/LocationListEntry.vue';
import { LocationService } from '@/Services/LocationService';
import { LocationEntry, RequestLocationDto } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Search } from 'lucide-vue-next';
import { PropType, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    locations: {
        type: Array as PropType<LocationEntry[]>,
        default: () => [] as LocationEntry[],
        required: false,
    },
});

const showStartButton = ref(false);
showStartButton.value = route().current('posts.create.start');

const filteredLocations = ref<LocationEntry[]>([]);
const search = ref<string>('');
const fetchingProgress = ref<RequestLocationDto | null>(null);
const currentPosition = ref<GeolocationPosition | null>(null);
const loading = ref<boolean>(false);

function fetchRequestLocation() {
    LocationService.getPosition(!!usePage().props.auth.user)
        .then((position) => {
            currentPosition.value = position;
            axios
                .get(
                    route('api.request-location.get', {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    }),
                )
                .then((data) => {
                    fetchingProgress.value = data.data;
                })
                .catch((error) => {
                    console.error('Failed to fetch request location:', error);
                });
        })
        .catch(() => {});
}

let debounceTimeout: ReturnType<typeof setTimeout>;

function filterLocations() {
    if (search.value.length <= 0 && fetchingProgress.value !== null) {
        filteredLocations.value = props.locations;
        return;
    }

    filteredLocations.value = Object.values(props.locations).filter(
        (location) =>
            location.name.toLowerCase().includes(search.value.toLowerCase()),
    );

    if (search.value.length >= 3 && currentPosition.value) {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            loading.value = true;
            axios
                .get(
                    route('api.location.search', {
                        latitude: currentPosition.value!.coords.latitude,
                        longitude: currentPosition.value!.coords.longitude,
                        query: search.value,
                    }),
                )
                .then((response) => {
                    if (response.data) {
                        const data = response.data as LocationEntry[];
                        const existingIds = new Set(
                            filteredLocations.value.map((l) => l.id),
                        );
                        const newLocations = data.filter(
                            (l) => !existingIds.has(l.id),
                        );
                        filteredLocations.value = [
                            ...filteredLocations.value,
                            ...newLocations,
                        ];
                    }
                    loading.value = false;
                })
                .catch((error) => {
                    loading.value = false;
                    console.error('Error fetching search results:', error);
                });
        }, 500);
    }
}
filterLocations();
fetchRequestLocation();

// fetch every 5 seconds
const fetchInterval = setInterval(() => {
    fetchRequestLocation();
    filterLocations();
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

watch(search, () => {
    filterLocations();
});
</script>

<template>
    <Head :title="t('new_post.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('new_post.title') }}
            </h2>
        </template>
        <div class="card bg-base-100 min-w-full shadow-md">
            <!-- Search Bar -->
            <div class="w-full p-8">
                <label class="input w-full">
                    <Search class="size-4 opacity-50" />
                    <input
                        v-model="search"
                        type="search"
                        class="grow"
                        :placeholder="
                            t('new_post.locations.filter_placeholder')
                        "
                        autofocus
                    />
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
                    />
                    {{ t('new_post.locations.fetching_osm') }}
                </li>
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    {{ t('new_post.locations.nearby') }}
                </li>

                <LocationListEntry
                    v-for="location in filteredLocations"
                    :key="location.id"
                    :data="{ location: location }"
                    :location
                    :show-start-button
                />
            </ul>
            <div v-if="loading" class="flex w-full justify-center p-8">
                <Loading />
            </div>
            <div
                v-if="
                    !loading &&
                    filteredLocations.length === 0 &&
                    fetchingProgress === null
                "
                class="p-8 text-center opacity-60"
            >
                {{ t('typeahead.no_suggestions') }}
            </div>
        </div>
    </AuthenticatedLayout>
</template>
