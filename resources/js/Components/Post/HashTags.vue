<script setup lang="ts">
import { PropType } from 'vue';

defineProps({
    hashTags: {
        type: Array as PropType<string[]>,
        required: true,
    },
});
</script>

<template>
    <div v-if="hashTags.length > 0" class="mt-2 flex flex-wrap gap-1.5">
        <span
            v-for="(tag, key) in hashTags"
            :key="key"
            class="bg-primary/10 text-primary hover:bg-primary/20 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors"
        >
            #{{ tag }}
        </span>
    </div>
</template>
