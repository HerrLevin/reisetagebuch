<script setup lang="ts">
defineProps<{
    value?: string;
}>();

const classes = 'label-text text-base-content';
</script>

<template>
    <label class="label">
        <span v-if="value" :class="classes">{{ value }}</span>
        <span v-else :class="classes"><slot /></span>
    </label>
</template>
