<script setup lang="ts">
defineProps<{
    likes: number;
    likedByUser: boolean;
}>();
</script>

<template>
    <span
        class="inline-flex w-6 content-end items-center justify-end text-xs"
        :class="{
            'text-red-500': likedByUser,
            'opacity-60': !likedByUser && likes > 0,
            'opacity-0': likes === 0,
        }"
    >
        {{ likes }}
    </span>
</template>
