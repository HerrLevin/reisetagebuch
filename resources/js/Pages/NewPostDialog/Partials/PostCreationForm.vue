<script setup lang="ts">
const model = defineModel({ default: '' });

defineProps({
    emoji: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
});

defineEmits(['cancel']);
</script>

<template>
    <div class="flex w-full items-center gap-4 p-8">
        <div class="text-3xl">{{ emoji }}</div>
        <div class="text-xl">{{ name }}</div>
    </div>
    <div class="w-full">
        <textarea
            class="textarea textarea-ghost bg-base-200 w-full"
            placeholder="Statustext"
            v-model="model"
        ></textarea>
    </div>
    <div class="flex w-full justify-end gap-4 px-8 py-4">
        <button class="btn btn-secondary" @click.prevent="$emit('cancel')">
            Cancel
        </button>
        <button class="btn btn-primary" type="submit">Post</button>
    </div>
</template>

<style scoped></style>
