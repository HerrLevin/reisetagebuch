<script setup lang="ts">
import LikesIndicator from '@/Components/TimelineEntry/LikesIndicator.vue';
import StarsIndicator from '@/Components/TimelineEntry/StarsIndicator.vue';

defineProps({
    username: {
        type: String,
        default: 'Max Mustermann',
    },
    stars: {
        type: Number,
        default: 0,
    },
    likes: {
        type: Number,
        default: 0,
    },
    location: {
        type: String,
        default: 'Karlsruhe, Deutschland',
    },
    venue: {
        type: String,
        default: 'Karlsruhe Hbf',
    },
    relativeCreatedAt: {
        type: String,
        default: '2023-10-01T12:00:00Z',
    },
});
</script>

<template>
    <div>
        <div>
            <div class="text-xs opacity-60">{{ username }}</div>
            <div class="font-semibold">{{ venue }}</div>
        </div>

        <div class="mt-1">
            <div class="flex items-center gap-2 text-xs">
                <span class="opacity-50">{{ location }}</span>
                <StarsIndicator :stars />
                <LikesIndicator :likes />
            </div>
            <div class="flex items-center text-xs opacity-60">
                {{ relativeCreatedAt }}
            </div>
        </div>
    </div>
</template>

<style scoped></style>
