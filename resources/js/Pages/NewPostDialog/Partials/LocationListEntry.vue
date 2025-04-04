<script setup lang="ts">
import { defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';
import { LocationEntry } from '@/types';

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
        <div class="text-3xl">{{ location.emoji }}</div>
        <div>
            <div>{{ location.name }}</div>
            <div class="text-xs uppercase opacity-60">
                {{ location.type }}
            </div>
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
        <button class="btn btn-square btn-ghost">
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
                    d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"
                />
            </svg>
        </button>
    </Link>
</template>

<style scoped></style>
