<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

// get url params
const urlParams = new URLSearchParams(window.location.search);

const emoji = urlParams.get('location[emoji]') || '📍';
const name = urlParams.get('location[name]') || '';
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
                <PostCreationForm
                    :name="name"
                    :emoji="emoji"
                    v-model="form.body"
                    @cancel="goBack"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
