<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { getBaseText, prettyDates } from '@/Services/PostTextService';
import { Visibility } from '@/types/enums';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Head, router } from '@inertiajs/vue3';
import { PropType, reactive } from 'vue';

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

function goBack() {
    window.history.back();
}

const form = reactive({
    id: '',
    body: '',
    visibility: Visibility.PUBLIC,
});

function submitForm() {
    router.patch(route('posts.update', props.post.id), form);
}

form.id = props.post.id;
form.body = props.post.body || '';
form.visibility = props.post.visibility;

const subtitle = `${getBaseText(props.post)} (${prettyDates(props.post)})`;
const title = `Edit Post`;
const fullTitle = `${title} · ${subtitle}`;
</script>

<template>
    <Head :title="fullTitle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">{{ fullTitle }}</h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <form @submit.prevent="submitForm">
                <PostCreationForm
                    v-model="form.body"
                    :name="title"
                    :subtitle="subtitle"
                    emoji="✍"
                    :default-visibility="form.visibility"
                    confirm-button-text="Save"
                    @cancel="goBack"
                    @select-visibility="(vis) => (form.visibility = vis)"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
