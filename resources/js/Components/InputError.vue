<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-error text-sm">
            {{ message }}
        </p>
    </div>
</template>
