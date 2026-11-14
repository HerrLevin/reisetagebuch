<script setup lang="ts">
import type { Component } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    title: string;
    value: number;
    description?: string;
    icon?: Component;
}>();

const { n } = useI18n();
</script>

<template>
    <div class="stat">
        <div v-if="icon" class="stat-figure text-primary">
            <component :is="icon" class="size-8" />
        </div>
        <div class="stat-title">{{ title }}</div>
        <div class="stat-value">{{ n(value) }}</div>
        <div v-if="description" class="stat-desc">{{ description }}</div>
    </div>
</template>
