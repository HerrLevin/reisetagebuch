<script setup lang="ts">
import { Icon } from '@/Icons/icon';

defineProps(Icon);
</script>
<template>
    <!-- Icon from https://github.com/tailwindlabs/heroicons/, licensed under MIT -->
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
            d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"
        />
    </svg>
</template>
