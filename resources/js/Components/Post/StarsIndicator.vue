<script setup lang="ts">
import { Star } from 'lucide-vue-next';

defineProps({
    stars: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <span v-if="stars > 0" class="inline-flex items-center gap-1 text-xs">
        <Star
            class="fill-warning inline-block size-[.875rem]"
            fill="currentColor"
        />

        {{ stars }}
    </span>
</template>
