<script setup lang="ts">
import TimelineEntry from '@/Components/TimelineEntry/TimelineEntry.vue';
import { Post } from '@/types';

defineProps({
    posts: {
        type: Array as () => Post[],
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
            :showRoute="post.showRoute"
        />
    </ul>
</template>
