<script setup lang="ts">
import { formatDelay } from '@/Services/TimeFormattingService';
import { PropType } from 'vue';

const props = defineProps({
    delay: {
        type: Number as PropType<number | null>,
        required: false,
        default: null,
    },
});

const absDelay = Math.abs(props.delay || 0);
</script>

<template>
    <p
        v-if="delay !== null"
        class="text-sm font-medium"
        :class="{
            'text-green-500': absDelay < 2,
            'text-yellow-500': absDelay < 5 && absDelay >= 2,
            'text-red-500': absDelay >= 5,
        }"
    >
        {{ formatDelay(delay) }}
    </p>
</template>
