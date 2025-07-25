<script setup lang="ts">
import LocationListEntryInfo from '@/Pages/NewPostDialog/Partials/LocationListEntryInfo.vue';
import { LocationEntry } from '@/types';
import { Link } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';
import {
    getEmojiFromTags,
    getName,
} from '../../../Services/LocationTypeService';

const props = defineProps({
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

const name = ref('');
const emoji = ref('');

emoji.value = getEmojiFromTags(props.location.tags);
name.value = getName(props.location as LocationEntry);

const linkData = ref({
    location: {
        emoji: emoji.value,
        name: name.value,
        id: props.location.id,
    },
});
</script>

<template>
    <Link
        :href="route('posts.create.post')"
        :data="linkData"
        as="li"
        class="list-row hover:bg-base-200 cursor-pointer"
    >
        <div class="text-3xl">{{ emoji }}</div>
        <div>
            <div>{{ name }}</div>
            <div v-if="location?.distance" class="text-xs uppercase opacity-60">
                {{ location!.distance }} m
            </div>
        </div>
        <!--
        for now, we don't need this button
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
        -->
        <LocationListEntryInfo :location>
            <template #activator="{ onClick }">
                <button
                    class="btn btn-square btn-ghost"
                    @click.prevent="onClick"
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
                            d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"
                        />
                    </svg>
                </button>
            </template>
        </LocationListEntryInfo>
    </Link>
</template>

<style scoped></style>
