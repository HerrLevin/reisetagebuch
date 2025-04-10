<script setup lang="ts">
import { LocationEntry } from '@/types';
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import { getEmojiFromTags } from '../../../Services/LocationTypeService';
import LocationListEntryInfo from '@/Pages/NewPostDialog/Partials/LocationListEntryInfo.vue';

defineProps({
    location: {
        type: Object,
        default: () => ({}) as LocationEntry,
    },
    showStartButton: {
        type: Boolean,
        default: true,
    },
    data: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Link
        :href="route('posts.create.post')"
        :data
        as="li"
        class="list-row hover:bg-base-200 cursor-pointer"
    >
        <div class="text-3xl">{{ getEmojiFromTags(location.tags) }}</div>
        <div>
            <div>{{ location.name }}</div>
            <div class="text-xs uppercase opacity-60"></div>
        </div>
        <Link
            v-if="showStartButton"
            :href="route('posts.create.route')"
            :data
            class="btn btn-square btn-ghost"
            as="button"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-[1.2em]"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"
                />
            </svg>
        </Link>
        <LocationListEntryInfo :location />
    </Link>
</template>

<style scoped></style>
