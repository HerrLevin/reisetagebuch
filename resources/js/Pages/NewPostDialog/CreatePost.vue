<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

// get url params
const urlParams = new URLSearchParams(window.location.search);

const emoji = urlParams.get('location[emoji]');
const name = urlParams.get('location[name]');
const id = urlParams.get('location[id]');

function goBack() {
    window.history.back();
}

const form = reactive({
    body: '',
    location: id,
});
function submitForm() {
    router.post(route('posts.create.post.store'), form);
}
</script>

<template>
    <Head title="Create Post" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Post</h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <form @submit.prevent="submitForm">
                <div class="flex w-full items-center gap-4 p-8">
                    <div class="text-3xl">{{ emoji }}</div>
                    <div class="text-xl">{{ name }}</div>
                </div>
                <div class="w-full">
                    <textarea
                        class="textarea textarea-ghost bg-base-200 w-full"
                        placeholder="Statustext"
                        v-model="form.body"
                    ></textarea>
                </div>
                <div class="flex w-full justify-end gap-4 px-8 py-4">
                    <button class="btn btn-secondary" @click.prevent="goBack">
                        Cancel
                    </button>
                    <button class="btn btn-primary" type="submit">Post</button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
