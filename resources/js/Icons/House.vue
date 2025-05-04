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
            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
        />
    </svg>
</template>
