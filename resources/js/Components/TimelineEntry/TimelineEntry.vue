<script setup lang="ts">
import LocationDisplay from '@/Components/TimelineEntry/LocationDisplay.vue';
import RouteDisplay from '@/Components/TimelineEntry/RouteDisplay.vue';
import { Post } from '@/types';

defineProps({
    post: {
        type: Object as () => Post,
        required: true,
    },
    picture: {
        type: String,
        default: '/assets/pexels-brenoanp-442535-1136575.jpg',
    },
    showRoute: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <li class="list-row">
        <div>
            <img
                class="rounded-box size-10"
                :src="picture"
                :alt="`Profile picture of ${post.user.name}`"
            />
        </div>
        <LocationDisplay v-if="post.location" :post />
        <RouteDisplay v-else-if="post.start" :post />
        <p v-if="post.body" class="list-col-wrap text-xs">
            {{ post.body }}
        </p>
        <!--        <LikeButton />-->
    </li>
</template>

<style scoped></style>
