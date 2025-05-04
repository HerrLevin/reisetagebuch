<script setup lang="ts">
import Interactions from '@/Components/Post/Interactions.vue';
import LocationDisplay from '@/Components/Post/LocationDisplay.vue';
import RouteDisplay from '@/Components/Post/RouteDisplay.vue';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { PropType } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
    picture: {
        type: String,
        default: '/assets/pexels-brenoanp-442535-1136575.jpg',
    },
});

let relativeCreatedAt = '';
const date = DateTime.fromISO(props.post?.created_at);

if (date.diffNow('days').days < -1) {
    relativeCreatedAt = date.toLocaleString();
} else {
    relativeCreatedAt = date.toRelative() || '';
}
</script>

<template>
    <div>
        <img
            class="rounded-box size-10"
            :src="picture"
            :alt="`Profile picture of ${post.user.name}`"
        />
    </div>
    <div>
        <div class="mb-1 text-xs opacity-60">
            <Link :href="route('profile.show', post.user.username)">
                {{ post.user.name }}
            </Link>
            ·
            <span class="text-xs opacity-60">
                {{ relativeCreatedAt }}
            </span>
        </div>
        <p v-if="post.body" class="list-col-wrap my-2 ps-3 text-xs">
            {{ post.body }}
        </p>
        <LocationDisplay
            v-if="isLocationPost(post)"
            :post="post as LocationPost"
        />
        <RouteDisplay
            v-else-if="isTransportPost(post)"
            :post="post as TransportPost"
        />
        <Interactions :post />
    </div>
</template>

<style scoped></style>
