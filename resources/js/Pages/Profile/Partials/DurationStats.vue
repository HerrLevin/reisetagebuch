<script setup lang="ts">
const props = defineProps({
    duration: {
        // duration in seconds
        type: Number,
        required: true,
    },
});

// Computed properties for days, hours, and minutes
const days = Math.floor(props.duration / 86400); // 86400 seconds in a day
const hours = Math.floor((props.duration % 86400) / 3600); // 3600 seconds in an hour
const minutes = Math.floor((props.duration % 3600) / 60); // Total minutes
const seconds = props.duration % 60;
</script>

<template>
    <span class="flex items-baseline">
        <template v-if="days">
            <b>{{ days }}</b>
            &nbsp;<span class="text-sm opacity-65">d</span>&nbsp;
        </template>
        <template v-if="hours || days">
            <b v-show="hours || days">{{ hours }}</b>
            &nbsp; <span class="text-sm opacity-65">h</span>&nbsp;
        </template>
        <template v-if="minutes || hours || days">
            <b>{{ minutes }}</b>
            &nbsp;<span class="text-sm opacity-65">m</span>
        </template>
        <template v-if="seconds">
            <b>{{ seconds }}</b>
            &nbsp;<span class="text-sm opacity-65">s</span>
        </template>
    </span>
</template>
