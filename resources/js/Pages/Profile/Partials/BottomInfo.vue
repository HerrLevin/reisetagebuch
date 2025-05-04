<script setup lang="ts">
import Calendar from '@/Icons/Calendar.vue';
import Link from '@/Icons/Link.vue';
</script>

<template>
    <div class="flex gap-3">
        <div class="flex flex-wrap gap-x-5 gap-y-2 opacity-65">
            <div class="flex items-center gap-1">
                <Calendar class="h-4 w-4" />
                <span class="truncate text-sm">Joined Mar 2027</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-x-5 gap-y-2 opacity-65">
            <div class="flex items-center gap-1">
                <Link class="h-4 w-4" />
                <a class="link link-hover link-primary truncate text-sm"
                    >traewelling.de/@HerrLevin_</a
                >
            </div>
        </div>
    </div>
</template>
