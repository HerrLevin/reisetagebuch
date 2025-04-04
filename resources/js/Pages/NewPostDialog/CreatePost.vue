<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Post" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Post</h2>
        </template>

        <div class="card bg-base-100 min-w-full p-8 shadow-md">
            <div class="p-6">You're logged in!</div>
        </div>
    </AuthenticatedLayout>
</template>
