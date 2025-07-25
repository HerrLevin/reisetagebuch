<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

function goBack() {
    window.history.back();
}

const form = reactive({
    body: '',
});

function submitForm() {
    router.post(route('posts.create.text-post.store'), form);
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
                    v-model="form.body"
                    name="New Post"
                    emoji="✍"
                    @cancel="goBack"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
