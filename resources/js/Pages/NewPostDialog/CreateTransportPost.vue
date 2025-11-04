<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { getEmoji } from '@/Services/DepartureTypeService';
import { TransportMode, Visibility } from '@/types/enums';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
    visibility: Visibility.PUBLIC,
    tags: [] as string[],
});

function submitForm() {
    router.post(route('posts.create.transport-post.store'), form);
}
</script>

<template>
    <Head :title="t('new_post.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('new_post.title') }}
            </h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <form @submit.prevent="submitForm">
                <PostCreationForm
                    v-model="form.body"
                    :name="title"
                    :emoji="emoji"
                    @cancel="goBack"
                    @select-visibility="
                        (visibility) => (form.visibility = visibility)
                    "
                    @update:tags="(tags) => (form.tags = tags)"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
