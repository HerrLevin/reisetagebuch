<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { Visibility } from '@/types/enums';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
    visibility: Visibility.PUBLIC,
});

function submitForm() {
    router.post(route('posts.create.post.store'), form);
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
                    :name="name"
                    :emoji="emoji"
                    @cancel="goBack"
                    @select-visibility="
                        (visibility) => (form.visibility = visibility)
                    "
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
