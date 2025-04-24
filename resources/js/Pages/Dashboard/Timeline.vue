<script setup lang="ts">
import TimelineEntry from '@/Components/TimelineEntry/TimelineEntry.vue';
import { Post } from '@/types';
import type { PropType } from 'vue';

defineProps({
    posts: {
        type: Array as PropType<Post[]>,
        default: () => [],
    },
});
</script>

<template>
    <ul class="list">
        <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
            Your Dashboard
        </li>
        <TimelineEntry
            v-for="post in posts"
            :key="post.id"
            :post="post"
            :showRoute="false"
        />
    </ul>
</template>
