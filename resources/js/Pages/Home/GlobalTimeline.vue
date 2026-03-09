<script setup lang="ts">
import { api } from '@/api';
import Loading from '@/Components/Loading.vue';
import Post from '@/Components/Post/Post.vue';
import { ChevronsUpDown } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
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
        .getGlobalTimeline({ cursor: nextCursor.value || undefined })
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
        <li class="p-4 pb-2 text-xs tracking-wide">
            <RouterLink :to="{ name: 'home' }" class="btn btn-sm">
                {{ t('pages.timeline.global_timeline') }}
                <ChevronsUpDown class="ml-1 h-4 w-4" />
            </RouterLink>
        </li>
        <li v-for="post in posts" :key="post.id">
            <RouterLink
                class="list-row hover-list-entry cursor-pointer"
                :to="`/posts/${post.id}`"
            >
                <Post :post="post" @delete:post="removePost(post.id)"></Post>
            </RouterLink>
        </li>
        <li v-show="!loading && !!nextCursor" class="p-4 text-center">
            <button class="btn btn-ghost w-full" @click="loadPosts()">
                {{ t('common.load_more') }}
            </button>
        </li>
        <Loading v-show="loading" class="m-4 mx-auto" />
    </ul>
</template>
