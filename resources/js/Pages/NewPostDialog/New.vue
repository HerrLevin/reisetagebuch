<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import LocationListEntry from '@/Pages/NewPostDialog/Partials/LocationListEntry.vue';

const demoLocations = [
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
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
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

                <LocationListEntry
                    v-for="location in demoLocations"
                    :emoji="location.emoji"
                    :name="location.name"
                    :type="location.type"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
