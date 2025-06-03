<script setup lang="ts">
import { getEmoji } from '@/Services/DepartureTypeService';
import { TransportPost } from '@/types/PostTypes';
import type { PropType } from 'vue';

defineProps({
    post: {
        type: Object as PropType<TransportPost>,
        required: true,
    },
});

function getTime(date: string): string {
    const dateTime = new Date(date);
    return dateTime.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <div>
        <div class="grid grid-cols-2 gap-0 pb-0">
            <div class="text-left">
                <div
                    class="mb-2 line-clamp-2 leading-none font-semibold overflow-ellipsis"
                >
                    {{ post.originStop.location.name }}
                </div>
            </div>
            <div class="text-right">
                <div
                    class="mb-2 line-clamp-2 leading-none font-semibold overflow-ellipsis"
                >
                    {{ post.destinationStop.location.name }}
                </div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-0 pb-0">
            <div class="text-left">
                <p class="text-muted-foreground text-sm font-medium">
                    {{ getTime(post.originStop.departureTime!) }}
                </p>
            </div>
            <div class="self-end text-center">
                <p class="text-muted-foreground text-sm font-medium">
                    {{ post.trip.lineName }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-muted-foreground text-sm font-medium">
                    {{ getTime(post.destinationStop.arrivalTime!) }}
                </p>
            </div>
        </div>
        <div class="flex w-full flex-col">
            <div class="divider divider-dashed mt-0">
                {{ getEmoji(post.trip.mode!) }}
            </div>
        </div>
    </div>
</template>

<style scoped>
.divider-dashed {
    &::before,
    &::after {
        background: repeating-linear-gradient(
            90deg,
            transparent,
            transparent 5px,
            color-mix(in oklab, var(--color-base-content) 10%, transparent) 5px,
            color-mix(in oklab, var(--color-base-content) 10%, transparent) 10px
        );
    }
}
</style>
