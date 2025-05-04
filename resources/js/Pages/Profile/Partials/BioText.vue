<script setup lang="ts">
defineProps({
    text: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div>
        <p class="line-clamp-15 overflow-ellipsis whitespace-pre-wrap">
            {{ text }}
        </p>
    </div>
</template>
