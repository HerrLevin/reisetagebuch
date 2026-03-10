<script setup lang="ts">
defineProps<{
    postId: string;
    likes: number;
    likedByUser: boolean;
}>();
</script>

<template>
    <RouterLink
        class="inline-flex w-6 content-end items-center justify-end text-xs font-bold"
        :class="{
            'text-red-500': likedByUser,
            'opacity-60': !likedByUser && likes > 0,
            'opacity-0': likes === 0,
        }"
        :to="{ name: 'posts.likes', params: { postId: postId } }"
    >
        {{ likes }}
    </RouterLink>
</template>
