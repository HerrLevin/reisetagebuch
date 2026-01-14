<script setup lang="ts">
import Loading from '@/Components/Loading.vue';
import Post from '@/Components/Post/Post.vue';
import ProfileWrapper from '@/Pages/Profile/ProfileWrapper.vue';
import type { UserDto } from '@/types';
import type { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{ username: string }>();

const posts = ref<Array<BasePost | TransportPost | LocationPost>>([]);
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);
const user = ref<UserDto | null>(null);
const loading = ref(true);
const userLoading = ref(true);

const loadProfileData = async () => {
    loading.value = true;
    try {
        axios.get('/api/profile/' + props.username).then((response) => {
            user.value = response.data;
            userLoading.value = false;
        });
    } catch (error) {
        console.error('Error loading profile data:', error);
    } finally {
        loading.value = false;
    }
};

const loadPosts = async () => {
    loading.value = true;
    try {
        const response = await axios.get(
            '/api/profile/' + props.username + '/posts',
            {
                params: {
                    cursor: nextCursor.value,
                },
            },
        );
        posts.value.push(...response.data.items);

        if (response.data.nextCursor === nextCursor.value) {
            nextCursor.value = null;
            return;
        }
        nextCursor.value = response.data.nextCursor;
    } catch (error) {
        console.error('Error loading posts:', error);
    } finally {
        loading.value = false;
    }
};

loadProfileData();
loadPosts();
</script>

<template>
    <Head v-if="user" :title="t('profile.profile_of', { name: user.name })" />

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
                <li v-show="!loading && nextCursor" class="p-4 text-center">
                    <button class="btn btn-ghost w-full" @click="loadPosts()">
                        {{ t('common.load_more') }}
                    </button>
                </li>
            </ul>
            <!--<InfiniteScroller :only="['posts']" />-->
            <Loading v-if="loading" class="mx-auto my-4"></Loading>
        </div>
    </ProfileWrapper>
</template>
