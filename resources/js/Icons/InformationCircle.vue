<script setup lang="ts">
import { Icon } from '@/Icons/icon';

defineProps(Icon);
</script>
<template>
    <!-- Icon from https://github.com/tailwindlabs/heroicons/, licensed under MIT -->
    <!-- eslint-disable -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        :viewBox
        :stroke-width="strokeWidth"
        :stroke="stroke"
        :class
    >
        <!-- eslint-enable -->
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"
        />
    </svg>
</template>
