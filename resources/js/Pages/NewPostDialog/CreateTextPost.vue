<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Visibility } from '../../../types/Api.gen';

const { t } = useI18n();
const vueRouter = useRouter();

useTitle(t('new_post.title'));

const loading = ref(false);

const form = reactive({
    body: '',
    visibility: Visibility.Public,
    tags: [] as string[],
});

function submitForm() {
    loading.value = true;
    api.posts
        .storeTextPost(form)
        .then((response) => {
            const postId = response.data.id;
            vueRouter.push(`/posts/${postId}`);
        })
        .finally(() => {
            loading.value = false;
        });
}
</script>

<template>
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
                    :name="t('new_post.title')"
                    emoji="✍"
                    @cancel="vueRouter.back()"
                    @select-visibility="
                        (visibility) => (form.visibility = visibility)
                    "
                    @update:tags="(tags) => (form.tags = tags)"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
