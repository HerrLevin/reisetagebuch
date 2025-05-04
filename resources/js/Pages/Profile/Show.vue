<script setup lang="ts">
import Post from '@/Components/Post/Post.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AvatarMenu from '@/Pages/Profile/Partials/AvatarMenu.vue';
import Banner from '@/Pages/Profile/Partials/Banner.vue';
import BioText from '@/Pages/Profile/Partials/BioText.vue';
import BottomInfo from '@/Pages/Profile/Partials/BottomInfo.vue';
import ProfileMenu from '@/Pages/Profile/Partials/ProfileMenu.vue';
import Statistics from '@/Pages/Profile/Partials/Statistics.vue';
import type { UserDto } from '@/types';
import type { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Head, Link } from '@inertiajs/vue3';
import type { PropType } from 'vue';

defineProps({
    posts: {
        type: Array as PropType<Array<BasePost | TransportPost | LocationPost>>,
        default: () => [],
    },
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Profile</h2>
        </template>
        <Banner />

        <div class="mb-4 space-y-1 px-5 md:px-0">
            <AvatarMenu />
            <div class="-mt-1">
                <div class="flex items-center">
                    <h3 class="truncate text-2xl font-bold">{{ user.name }}</h3>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm">@{{ user.username }}</span>
                </div>
            </div>
            <Statistics :user="user" />
            <BioText :user="user" />
            <BottomInfo :user="user" />
            <ProfileMenu />
        </div>

        <div class="card bg-base-100 min-w-full shadow-md">
            <ul class="list">
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
        </div>
    </AuthenticatedLayout>
</template>
