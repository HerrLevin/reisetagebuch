<script setup lang="ts">
import Post from '@/Components/Post/Post.vue';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Link } from '@inertiajs/vue3';
import type { PropType } from 'vue';
import InfiniteScroller from '@/Components/InfiniteScroller.vue';

defineProps({
    posts: {
        type: Array as PropType<Array<BasePost | TransportPost | LocationPost>>,
        default: () => [],
    },
});
</script>

<template>
    <ul class="list">
        <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
            Your Dashboard
        </li>
        <li v-for="post in posts" :key="post.id">
            <Link
                class="list-row hover-list-entry cursor-pointer"
                as="div"
                :href="route('posts.show', post.id)"
            >
                <Post :post="post"></Post>
            </Link>
        </li>
        <InfiniteScroller :only="['posts']" />
    </ul>
</template>

<style scoped>
.hover-list-entry {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}
.hover-list-entry::after {
    border-color: var(--color-base-300);
}
.hover-list-entry:hover {
    background-color: var(--color-base-200);
}
</style>
