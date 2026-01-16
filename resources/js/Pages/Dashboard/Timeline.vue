<script setup lang="ts">
import { api } from '@/app';
import Loading from '@/Components/Loading.vue';
import Post from '@/Components/Post/Post.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { BasePost, LocationPost, TransportPost } from '../../../types/Api.gen';

const { t } = useI18n();

const posts = ref<Array<BasePost | TransportPost | LocationPost>>([]);
const loading = ref(false);
const nextCursor = ref<string | null>(null);

function loadPosts() {
    if (loading.value) {
        return;
    }

    loading.value = true;
    api.timeline
        .timeline({ cursor: nextCursor.value || undefined })
        .then((response) => {
            posts.value.push(...response.data.items);
            if (response.data.nextCursor === nextCursor.value) {
                nextCursor.value = null;
                return;
            }
            nextCursor.value = response.data.nextCursor;
        })
        .finally(() => {
            loading.value = false;
        });
}

function removePost(postId: string): void {
    const index = posts.value.findIndex((post) => post.id === postId);
    if (index !== -1) {
        posts.value.splice(index, 1);
    }
}

loadPosts();
</script>

<template>
    <ul class="list">
        <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
            {{ t('pages.timeline.your_timeline') }}
        </li>
        <li v-for="post in posts" :key="post.id">
            <Link
                class="list-row hover-list-entry cursor-pointer"
                as="div"
                :href="route('posts.show', post.id)"
            >
                <Post :post="post" @delete:post="removePost(post.id)"></Post>
            </Link>
        </li>
        <li v-show="!loading && !!nextCursor" class="p-4 text-center">
            <button class="btn btn-ghost w-full" @click="loadPosts()">
                {{ t('common.load_more') }}
            </button>
        </li>
        <Loading v-show="loading" class="m-4 mx-auto" />
        <!--        <InfiniteScroller :only="['posts']" />-->
    </ul>
</template>
