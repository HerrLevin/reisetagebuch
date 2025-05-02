<script setup lang="ts">
import ContextMenu from '@/Components/Post/ContextMenu.vue';
import ArrowUpOnSquare from '@/Icons/ArrowUpOnSquare.vue';
import Heart from '@/Icons/Heart.vue';
import { BasePost } from '@/types/PostTypes';
import { PropType } from 'vue';

defineProps({
    post: {
        type: Object as PropType<BasePost>,
        required: true,
    },
});
</script>

<template>
    <div class="flex items-center justify-between">
        <button class="btn btn-ghost btn-sm btn-circle">
            <Heart />
        </button>

        <button class="btn btn-ghost btn-sm btn-circle">
            <ArrowUpOnSquare />
        </button>

        <ContextMenu :post />
    </div>
</template>

<style scoped></style>
