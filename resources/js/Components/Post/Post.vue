<script setup lang="ts">
import Interactions from '@/Components/Post/Interactions.vue';
import LocationDisplay from '@/Components/Post/LocationDisplay.vue';
import RouteDisplay from '@/Components/Post/RouteDisplay.vue';
import { getIcon } from '@/Services/VisibilityMapping';
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
});

let relativeCreatedAt;
const date = DateTime.fromISO(props.post?.created_at);

if (date.diffNow('days').days < -1) {
    relativeCreatedAt = date.toLocaleString();
} else {
    relativeCreatedAt = date.toRelative() || '';
}
</script>

<template>
    <div class="avatar">
        <div class="bg-primary size-10 rounded-xl">
            <img
                v-if="post.user.avatar"
                :src="post.user.avatar"
                :alt="`Profile picture of ${post.user.name}`"
            />
        </div>
    </div>
    <div>
        <div class="mb-1 text-xs opacity-60">
            <Link :href="route('profile.show', post.user.username)">
                {{ post.user.name }}
            </Link>
            ·
            <span class="text-xs opacity-60">
                <component
                    :is="getIcon(post.visibility)"
                    class="iconSize inline"
                />
                {{ relativeCreatedAt }}
            </span>
        </div>
        <p
            v-if="post.body"
            class="list-col-wrap my-2 ps-3 text-xs whitespace-pre-wrap"
        >
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

<style scoped>
.iconSize {
    width: 1em;
    height: 1em;
}
</style>
