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
            d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
    </svg>
</template>
