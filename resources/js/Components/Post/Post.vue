<script setup lang="ts">
import LocationDisplay from '@/Components/Post/LocationDisplay.vue';
import RouteDisplay from '@/Components/Post/RouteDisplay.vue';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { PropType } from 'vue';

defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
    picture: {
        type: String,
        default: '/assets/pexels-brenoanp-442535-1136575.jpg',
    },
});
</script>

<template>
    <div>
        <img
            class="rounded-box size-10"
            :src="picture"
            :alt="`Profile picture of ${post.user.name}`"
        />
    </div>
    <LocationDisplay v-if="isLocationPost(post)" :post="post as LocationPost" />
    <RouteDisplay
        v-else-if="isTransportPost(post)"
        :post="post as TransportPost"
    />
    <p v-if="post.body" class="list-col-wrap text-xs">
        {{ post.body }}
    </p>
</template>

<style scoped></style>
