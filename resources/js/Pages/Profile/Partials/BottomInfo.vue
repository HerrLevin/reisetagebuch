<script setup lang="ts">
import Calendar from '@/Icons/Calendar.vue';
import Link from '@/Icons/Link.vue';
import type { UserDto } from '@/types';
import { DateTime } from 'luxon';
import { PropType } from 'vue';

defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});

function getDateString(date: string): string {
    const dateObj = DateTime.fromISO(date);

    return dateObj.toLocaleString({
        month: 'long',
        year: '2-digit',
    });
}
</script>

<template>
    <div class="flex gap-3">
        <div class="flex flex-wrap gap-x-5 gap-y-2 opacity-65">
            <div class="flex items-center gap-1">
                <Calendar class="h-4 w-4" />
                <span class="truncate text-sm">
                    Joined {{ getDateString(user.createdAt) }}
                </span>
            </div>
        </div>
        <div
            v-if="user.website"
            class="flex flex-wrap gap-x-5 gap-y-2 opacity-65"
        >
            <div class="flex items-center gap-1">
                <Link class="h-4 w-4" />
                <a
                    class="link link-hover link-primary max-w-60 truncate text-sm"
                >
                    <a :href="user.website" target="_blank">
                        {{ user.website }}
                    </a>
                </a>
            </div>
        </div>
    </div>
</template>
