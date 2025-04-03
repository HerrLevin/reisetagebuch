<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps({
    emoji: {
        type: String,
        default: '🏘️',
    },
    name: {
        type: String,
        default: 'Oststadt',
    },
    type: {
        type: String,
        default: 'Neighborhood',
    },
});
</script>

<template>
    <li class="list-row cursor-pointer hover:bg-neutral-200">
        <div class="text-3xl">{{ emoji }}</div>
        <div>
            <div>{{ name }}</div>
            <div class="text-xs uppercase opacity-60">
                {{ type }}
            </div>
        </div>
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
                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"
                />
            </svg>
        </button>
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
    </li>
</template>

<style scoped></style>
