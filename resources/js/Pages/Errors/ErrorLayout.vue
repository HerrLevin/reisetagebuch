<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { RouteLocationRaw, RouterLink } from 'vue-router';

defineProps<{
    code: string;
    title: string;
    message: string;
    linkText: string;
    to: RouteLocationRaw;
}>();
</script>

<template>
    <AuthenticatedLayout>
        <div class="flex h-full flex-col items-center justify-center">
            <h2 class="mb-4 text-9xl font-extrabold text-gray-300">
                {{ code }}
            </h2>
            <h1 class="mb-4 text-4xl font-bold">
                {{ title }}
            </h1>
            <p class="mb-8 text-lg">
                {{ message }}
            </p>
            <slot name="default"></slot>
            <RouterLink :to class="text-blue-500 hover:underline">
                {{ linkText }}
            </RouterLink>
        </div>
    </AuthenticatedLayout>
</template>
