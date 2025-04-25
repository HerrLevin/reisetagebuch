<script setup lang="ts">
defineProps({
    delay: {
        type: Number,
        default: -1,
    },
    time: {
        type: String,
        default: '',
    },
    plannedTime: {
        type: String,
        default: '',
    },
    realTime: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        :class="{
            'text-warning': delay < 4 && delay >= 2,
            'text-success': delay < 2 && delay >= 0 && realTime,
            'text-error': delay >= 4,
        }"
    >
        {{ time }}
    </div>
    <div v-if="delay" class="line-through opacity-60">
        {{ plannedTime }}
    </div>
</template>

<style scoped></style>
