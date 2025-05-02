<script setup lang="ts">
import { Icon } from '@/Icons/icon';

defineProps(Icon);
</script>
<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        :viewBox
        :stroke-width="strokeWidth"
        stroke="currentColor"
        :class
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"
        />
    </svg>
</template>
