<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { TravelReason, Visibility } from '@/types/enums';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';

const { t } = useI18n();
const vueRouter = useRouter();
const route = useRoute();

useTitle(t('new_post.title'));

// get url params

const emoji = (route.query.emoji as string | undefined) || '📍';
const name = (route.query.name as string | undefined) || '';
const id = route.query.id as string | undefined;

const loading = ref(false);

const form = reactive({
    body: '',
    location: id,
    visibility: Visibility.PUBLIC,
    tags: [] as string[],
    travelReason: TravelReason.LEISURE,
});

function submitForm() {
    loading.value = true;
    api.posts
        .storeLocationPost(form)
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
                    :name="name"
                    :emoji="emoji"
                    :show-travel-reason="true"
                    :loading="loading"
                    @cancel="useRouter().back()"
                    @select-travel-reason="
                        (travelReason) => (form.travelReason = travelReason)
                    "
                    @select-visibility="
                        (visibility) => (form.visibility = visibility)
                    "
                    @update:tags="(tags) => (form.tags = tags)"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
