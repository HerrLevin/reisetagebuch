<script setup lang="ts">
import { PropType } from 'vue';
import { DateTime } from 'luxon';

defineProps({
    time: {
        type: Object as PropType<DateTime | null>,
        required: false,
        default: null,
    },
    format: {
        type: Object as PropType<Intl.DateTimeFormatOptions>,
        required: false,
        default: DateTime.TIME_24_SIMPLE,
    },
    delay: {
        type: Number as PropType<number | null>,
        required: false,
        default: null,
    },
    placeholder: {
        type: String,
        required: false,
        default: '--',
    },
});
</script>

<template>
    <p v-if="time" class="text-xs font-medium">
        {{ time.toLocaleString(format) }}
    </p>
    <p v-else class="text-xs font-medium opacity-50">
        {{ placeholder }}
    </p>
</template>
