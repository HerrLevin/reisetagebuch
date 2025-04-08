<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Timeline from '@/Pages/Dashboard/Timeline.vue';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Dashboard</h2>
        </template>

        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <Timeline />
        </div>
    </AuthenticatedLayout>
</template>
