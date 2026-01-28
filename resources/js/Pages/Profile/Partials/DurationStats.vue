<script setup lang="ts">
const props = defineProps({
    duration: {
        // duration in minutes
        type: Number,
        required: true,
    },
});

// Computed properties for days, hours, and minutes
const days = Math.floor(props.duration / 1440); // 1440 minutes in a day
const hours = Math.floor((props.duration % 1440) / 60); // 60 minutes in an hour
const minutes = props.duration % 60;
</script>

<template>
    <span class="flex">
        <template v-if="days">
            <b>{{ days }}</b>
            &nbsp;<span class="opacity-65">d</span>&nbsp;
        </template>
        <template v-if="hours || days">
            <b v-show="hours || days">{{ hours }}</b>
            &nbsp; <span class="opacity-65">h</span>&nbsp;
        </template>
        <b>{{ minutes }}</b>
        &nbsp;<span class="opacity-65">m</span>
    </span>
</template>
