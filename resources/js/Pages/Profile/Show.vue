<script setup lang="ts">
import { api } from '@/api';
import Loading from '@/Components/Loading.vue';
import Post from '@/Components/Post/Post.vue';
import ProfileWrapper from '@/Pages/Profile/ProfileWrapper.vue';
import type { UserDto } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { BasePost, LocationPost, TransportPost } from '../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps<{ username: string }>();

const posts = ref<Array<BasePost | TransportPost | LocationPost>>([]);
const nextCursor = ref<string | null>(null);
const user = ref<UserDto | null>(null);
const loading = ref(true);
const userLoading = ref(true);

const loadProfileData = async () => {
    loading.value = true;
    try {
        api.profile.getProfile(props.username).then((response) => {
            user.value = response.data;
            userLoading.value = false;
            loadPosts();
        });
    } catch (error) {
        console.error('Error loading profile data:', error);
    } finally {
        loading.value = false;
    }
};

const loadPosts = async () => {
    if (loading.value || !user.value) return;
    loading.value = true;
    api.users
        .postsForUser(user.value.id, {
            cursor: nextCursor.value || undefined,
        })
        .then((response) => {
            posts.value.push(...response.data.items);
            if (response.data.nextCursor === nextCursor.value) {
                nextCursor.value = null;
                return;
            }
            nextCursor.value = response.data.nextCursor;
        })
        .catch((error) => {
            console.error('Error loading posts:', error);
        })
        .finally(() => {
            loading.value = false;
        });
};

function deletePost(postId: string): void {
    const index = posts.value.findIndex((post) => post.id === postId);
    if (index !== -1) {
        posts.value.splice(index, 1);
    }
}

loadProfileData();
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
                        <Post
                            :post="post"
                            @delete:post="deletePost(post.id)"
                        ></Post>
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
