<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { getBaseText, prettyDates } from '@/Services/PostTextService';
import { TravelReason, Visibility } from '@/types/enums';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { Head, router } from '@inertiajs/vue3';
import { PropType, reactive } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
    body: '' as string | undefined,
    visibility: Visibility.PUBLIC,
    tags: [] as string[],
    travelReason: TravelReason.LEISURE,
});

function submitForm() {
    if (!form.body?.trim()) {
        form.body = undefined;
    }
    router.patch(route('posts.update', props.post.id), form);
}

form.id = props.post.id;
form.body = props.post.body || '';
form.visibility = props.post.visibility;
form.tags = props.post.hashTags || [];
form.travelReason = props.post.travelReason || TravelReason.LEISURE;

const subtitle = `${getBaseText(props.post)} (${prettyDates(props.post)})`;
const title = t('edit_post.title');
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
                    :confirm-button-text="t('verbs.save')"
                    :tags="form.tags"
                    :travel-reason="form.travelReason"
                    :show-travel-reason="
                        isTransportPost(post) || isLocationPost(post)
                    "
                    @select-travel-reason="
                        (reason) => (form.travelReason = reason)
                    "
                    @cancel="goBack"
                    @select-visibility="(vis) => (form.visibility = vis)"
                    @update:tags="(tags) => (form.tags = tags)"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
