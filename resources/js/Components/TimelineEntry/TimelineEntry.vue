<script setup lang="ts">
import LikeButton from '@/Components/TimelineEntry/LikeButton.vue';
import RouteDisplay from '@/Components/TimelineEntry/RouteDisplay.vue';
import LocationDisplay from '@/Components/TimelineEntry/LocationDisplay.vue';
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
    region: {
        type: String,
        default: 'Karlsruhe, Deutschland',
    },
    location: {
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
    showRoute: {
        type: Boolean,
        default: false,
    },
});

function relativeCreatedAt(): string {
    const date = DateTime.fromISO(props.createdAt);

    if (date.diffNow('days').days < -1) {
        return date.toLocaleString();
    }
    return date.toRelative() || '';
}
</script>

<template>
    <li class="list-row">
        <div>
            <img
                class="rounded-box size-10"
                :src="picture"
                :alt="`Profile picture of ${username}`"
            />
        </div>
        <LocationDisplay
            v-if="!showRoute"
            :stars
            :likes
            :region
            :location
            :body
            :relativeCreatedAt="relativeCreatedAt()"
        />
        <RouteDisplay v-else />
        <p v-if="body" class="list-col-wrap text-xs">
            {{ body }}
        </p>
        <LikeButton />
    </li>
</template>

<style scoped></style>
