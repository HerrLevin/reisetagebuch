<script setup lang="ts">
import { Heart } from 'lucide-vue-next';

defineProps({
    likes: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <span v-if="likes > 0" class="inline-flex items-center gap-1 text-xs">
        <Heart
            class="fill-error inline-block size-[.875rem]"
            fill="currentColor"
        />
        {{ likes }}
    </span>
</template>
