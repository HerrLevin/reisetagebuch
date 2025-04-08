<script setup lang="ts">
import LikeButton from '@/Components/TimelineEntry/LikeButton.vue';
import LikesIndicator from '@/Components/TimelineEntry/LikesIndicator.vue';
import StarsIndicator from '@/Components/TimelineEntry/StarsIndicator.vue';
import { DateTime } from 'luxon';

const props = defineProps({
    username: {
        type: String,
        default: 'Max Mustermann',
    },
    stars: {
        type: Number,
        default: 0,
    },
    likes: {
        type: Number,
        default: 0,
    },
    location: {
        type: String,
        default: 'Karlsruhe, Deutschland',
    },
    venue: {
        type: String,
        default: 'Karlsruhe Hbf',
    },
    createdAt: {
        type: String,
        default: '2023-10-01T12:00:00Z',
    },
    body: {
        type: String,
        default: '',
        required: false,
    },
    picture: {
        type: String,
        default: 'https://loremfaces.net/96/id/1.jpg',
    },
});

function relativeCreatedAt() {
    const date = DateTime.fromISO(props.createdAt);

    if (date.diffNow('days').days < -1) {
        return date.toLocaleString();
    }
    return date.toRelative();
}
</script>

<template>
    <li class="list-row">
        <div>
            <img class="rounded-box size-10" :src="picture" />
        </div>
        <div>
            <div>
                <div class="text-xs opacity-60">{{ username }}</div>
                <div class="font-semibold">{{ venue }}</div>
            </div>

            <div class="mt-1">
                <div class="flex items-center gap-2 text-xs">
                    <span class="opacity-50">{{ location }}</span>
                    <StarsIndicator :stars />
                    <LikesIndicator :likes />
                </div>
                <div class="flex items-center text-xs opacity-60">
                    {{ relativeCreatedAt() }}
                </div>
            </div>
        </div>
        <p v-if="body" class="list-col-wrap text-xs">
            {{ body }}
        </p>
        <LikeButton />
    </li>
</template>

<style scoped></style>
