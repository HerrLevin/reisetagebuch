<script setup lang="ts">
import { formatDelay } from '@/Services/TimeFormattingService';
import { PropType } from 'vue';

defineProps({
    delay: {
        type: Number as PropType<number | null>,
        required: false,
        default: null,
    },
});
</script>

<template>
    <p v-if="delay !== null" class="text-xs font-medium">
        (<span
            :class="{
                'text-pink-500': delay < 0,
                'text-green-500': delay < 2,
                'text-yellow-500': delay < 5 && delay >= 2,
                'text-red-500': delay >= 5,
            }"
            >{{ formatDelay(delay) }}</span
        >)
    </p>
</template>
