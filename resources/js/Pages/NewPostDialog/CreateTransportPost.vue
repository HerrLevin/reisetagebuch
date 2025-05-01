<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { getEmoji } from '@/Services/DepartureTypeService';
import { TransportMode } from '@/types/enums';

// get url params
const urlParams = new URLSearchParams(window.location.search);

const mode = urlParams.get('stopMode');
const name = urlParams.get('stopName') || '';
const line = urlParams.get('lineName') || '';
const title = line ? `${line} ➜ ${name}` : name;
let emoji = '';

if (mode) {
    emoji = getEmoji(mode as TransportMode);
}

const tripId = urlParams.get('tripId');
const startId = urlParams.get('startId');
const startTime = urlParams.get('startTime');
const stopId = urlParams.get('stopId');
const stopTime = urlParams.get('stopTime');

function goBack() {
    window.history.back();
}

const form = reactive({
    body: '',
    tripId: tripId,
    startId: startId,
    startTime: startTime,
    stopId: stopId,
    stopTime: stopTime,
});

function submitForm() {
    router.post(route('posts.create.transport-post.store'), form);
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
                    :name="title"
                    :emoji="emoji"
                    v-model="form.body"
                    @cancel="goBack"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
