<script setup lang="ts">
import LocationListEntryInfo from '@/Pages/NewPostDialog/Partials/LocationListEntryInfo.vue';
import { getEmojiFromTags, getName } from '@/Services/LocationTypeService';
import { LocationEntry } from '@/types';
import { CircleQuestionMark } from 'lucide-vue-next';
import { defineProps, ref } from 'vue';
import { RouterLink } from 'vue-router';

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

const params = {
    emoji: emoji.value,
    name: name.value,
    id: props.location.id,
};
</script>

<template>
    <RouterLink
        v-slot="{ navigate }"
        :to="{ path: '/posts/create', query: params }"
    >
        <li class="list-row hover:bg-base-200 cursor-pointer" @click="navigate">
            <div class="text-3xl">{{ emoji }}</div>
            <div>
                <div>{{ name }}</div>
                <div
                    v-if="location?.distance && location?.distance < 1000"
                    class="text-xs uppercase opacity-60"
                >
                    {{ location!.distance }} m
                </div>
                <div
                    v-else-if="location?.distance"
                    class="text-xs uppercase opacity-60"
                >
                    {{ (location!.distance / 1000).toFixed(1) }} km
                </div>
            </div>
            <LocationListEntryInfo :location>
                <template #activator="{ onClick }">
                    <button
                        class="btn btn-square btn-ghost"
                        @click.prevent="onClick"
                    >
                        <CircleQuestionMark class="size-5" />
                    </button>
                </template>
            </LocationListEntryInfo>
        </li>
    </RouterLink>
</template>
