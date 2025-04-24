<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Timeline from '@/Pages/Dashboard/Timeline.vue';
import { Post } from '@/types';
import { Head } from '@inertiajs/vue3';
import type { PropType } from 'vue';

defineProps({
    posts: {
        type: Array as PropType<Post[]>,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Dashboard</h2>
        </template>

        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <Timeline :posts />
        </div>
    </AuthenticatedLayout>
</template>
