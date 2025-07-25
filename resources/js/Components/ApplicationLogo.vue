<script setup lang="ts">
defineProps({
    class: {
        type: String,
        default: 'w-20 h-20 fill-current',
    },
});
</script>
<template>
    <!-- eslint-disable-next-line -->
    <img src="/assets/logo.webp" alt="Logo" :class />
</template>
