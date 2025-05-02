<script setup lang="ts">
import LocationDisplay from '@/Components/Post/LocationDisplay.vue';
import RouteDisplay from '@/Components/Post/RouteDisplay.vue';
import type { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
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

const isLocationPost = (post: BasePost): post is LocationPost => {
    return (post as LocationPost).location !== undefined;
};

const isTransportPost = (post: BasePost): post is TransportPost => {
    return (post as TransportPost).start !== undefined;
};
console.log(typeof props.post);
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
