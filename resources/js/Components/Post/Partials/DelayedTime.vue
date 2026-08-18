<script setup lang="ts">
import { PropType } from 'vue';
import { DateTime } from 'luxon';
import Time from '@/Components/Post/Partials/Time.vue';

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
});
</script>

<template>
    <Time
        v-if="delay !== null"
        :time="time"
        :format="format"
        :class="{
            'text-pink-500': delay < 0,
            'text-green-500': delay < 2,
            'text-yellow-500': delay < 5 && delay >= 2,
            'text-red-500': delay >= 5,
        }"
    />
    <Time v-else :time="time" :format="format" />
</template>
