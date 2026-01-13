<script setup lang="ts">
import Post from '@/Components/Post/Post.vue';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Link } from '@inertiajs/vue3';
import type { PropType } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    posts: {
        type: Array as PropType<Array<BasePost | TransportPost | LocationPost>>,
        default: () => [],
    },
    showNext: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['next']);
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
                <Post :post="post"></Post>
            </Link>
        </li>
        <li v-show="showNext" class="p-4 text-center">
            <button class="btn btn-ghost w-full" @click="emit('next')">
                {{ t('common.load_more') }}
            </button>
        </li>
        <!--        <InfiniteScroller :only="['posts']" />-->
    </ul>
</template>
