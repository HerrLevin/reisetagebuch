<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import LocationListEntry from '@/Pages/NewPostDialog/Partials/LocationListEntry.vue';
import { LocationEntry } from '@/types';
import { ref } from 'vue';

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
</script>

<template>
    <Head title="Dashboard" />

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
                        type="search"
                        class="grow"
                        placeholder="Search"
                        @keyup="filterLocations"
                        v-model="search"
                        autofocus
                    />
                    <!--                    <kbd class="kbd kbd-sm">⌘</kbd>-->
                    <!--                    <kbd class="kbd kbd-sm">K</kbd>-->
                </label>
            </div>

            <!-- Results -->
            <ul class="list">
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    Locations nearby
                </li>

                <LocationListEntry
                    :data="{ location: location }"
                    v-for="location in filteredLocations"
                    :location
                    :showStartButton
                    :key="location.id"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
