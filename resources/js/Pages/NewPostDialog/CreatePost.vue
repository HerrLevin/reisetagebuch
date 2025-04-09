<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

// get url params
const urlParams = new URLSearchParams(window.location.search);

const emoji = urlParams.get('location[emoji]');
const name = urlParams.get('location[name]');
function goBack() {
    window.history.back();
}
</script>

<template>
    <Head title="Create Post" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Post</h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <div class="flex w-full items-center gap-4 p-8">
                <div class="text-3xl">{{ emoji }}</div>
                <div class="text-xl">{{ name }}</div>
            </div>
            <div class="w-full">
                <textarea
                    class="textarea textarea-ghost bg-base-200 w-full"
                    placeholder="Statustext"
                ></textarea>
            </div>
            <div class="flex w-full justify-end gap-4 px-8 py-4">
                <button class="btn btn-secondary" @click="goBack">
                    Cancel
                </button>
                <button class="btn btn-primary">Post</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
