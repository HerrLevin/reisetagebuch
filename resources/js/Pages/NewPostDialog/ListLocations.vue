<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import LocationListEntry from '@/Pages/NewPostDialog/Partials/LocationListEntry.vue';
import { LocationEntry } from '@/types';
import { ref } from 'vue';

const demoLocations = ref([
    {
        emoji: '🏘️',
        name: 'Oststadt',
        type: 'Neighborhood',
    },
    {
        emoji: '🥨',
        name: 'Visel',
        type: 'Bakery',
    },
    {
        emoji: '🚉',
        name: 'Gottesauer Platz / BGV',
        type: 'Tram Stop',
    },
    {
        name: 'Edeka',
        type: 'Supermarket',
        emoji: '🛒',
    },
    {
        name: 'Disco',
        type: 'Club',
        emoji: '💃',
    },
    {
        name: 'Tulla Brunnen',
        type: 'Park',
        emoji: '🌳',
    },
    {
        name: 'EnBW',
        type: 'Office Building',
        emoji: '🏢',
    },
    {
        emoji: '🏙️',
        name: 'Karlsruhe',
        type: 'City',
    },
] as LocationEntry[]);
const props = defineProps({
    venues: {
        type: Array,
        default: () => [],
        required: false,
    },
});

const showStartButton = ref(false);
showStartButton.value = route().current('posts.create.start');

if (!showStartButton.value) {
    // sort random
    demoLocations.value = demoLocations.value.sort(() => Math.random() - 0.5);
}
console.log(props.venues);
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
                    <input type="search" class="grow" placeholder="Search" />
                    <kbd class="kbd kbd-sm">⌘</kbd>
                    <kbd class="kbd kbd-sm">K</kbd>
                </label>
            </div>

            <!-- Results -->
            <ul class="list">
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    Locations nearby
                </li>

                <!-- todo: use id as key -->
                <LocationListEntry
                    :data="{ location: location }"
                    v-for="location in demoLocations"
                    :location
                    :showStartButton
                    :key="location.name"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
