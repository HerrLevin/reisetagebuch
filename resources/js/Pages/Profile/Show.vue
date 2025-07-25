<script setup lang="ts">
import InfiniteScroller from '@/Components/InfiniteScroller.vue';
import Post from '@/Components/Post/Post.vue';
import ProfileWrapper from '@/Pages/Profile/ProfileWrapper.vue';
import type { UserDto } from '@/types';
import type { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Head, Link } from '@inertiajs/vue3';
import type { PropType } from 'vue';

defineProps({
    posts: {
        type: Array as PropType<Array<BasePost | TransportPost | LocationPost>>,
        default: () => [],
    },
    nextCursor: {
        type: String,
        nullable: true,
        default: '',
    },
    prevCursor: {
        type: String,
        nullable: true,
        default: '',
    },
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Profile" />

    <ProfileWrapper :user="user">
        <div class="card bg-base-100 min-w-full shadow-md">
            <ul v-if="posts.length > 0" class="list">
                <li v-for="post in posts" :key="post.id">
                    <Link
                        class="list-row hover-list-entry cursor-pointer"
                        as="div"
                        :href="route('posts.show', post.id)"
                    >
                        <Post :post="post"></Post>
                    </Link>
                </li>
            </ul>
            <InfiniteScroller :only="['posts']" />
        </div>
    </ProfileWrapper>
</template>
