<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { getBaseText, prettyDates } from '@/Services/PostTextService';
import { TravelReason, TravelRole, Visibility } from '@/types/enums';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
});

const post = ref<BasePost | TransportPost | LocationPost | null>(null);
const title = ref<string>(t('edit_post.title'));
const subtitle = ref<string>('');
const fullTitle = ref<string>(t('edit_post.title'));

function goBack() {
    window.history.back();
}

function fetchPost() {
    axios
        .get(`/api/posts/${props.postId}`)
        .then((response) => {
            post.value = response.data as
                | BasePost
                | TransportPost
                | LocationPost;
            prefillForm();
        })
        .catch(() => {
            post.value = null;
        });
}

const form = reactive({
    id: '',
    body: '' as string | undefined,
    visibility: Visibility.PUBLIC,
    tags: [] as string[],
    travelReason: TravelReason.LEISURE,
    vehicleIds: [] as string[],
    metaTripId: null as string | null,
    travelRole: null as TravelRole | null,
});

function submitForm() {
    if (!post.value) {
        return;
    }
    if (!form.body?.trim()) {
        form.body = undefined;
    }
    router.patch(route('posts.update', post.value.id), form);
}

function prefillForm() {
    if (!post.value) {
        return;
    }

    form.id = post.value.id;
    form.body = post.value.body || '';
    form.visibility = post.value.visibility;
    form.tags = post.value.hashTags || [];
    form.travelReason = post.value.travelReason || TravelReason.LEISURE;

    const vehicleIds = post.value.metaInfos['rtb:vehicle_id'];
    if (Array.isArray(vehicleIds)) {
        form.vehicleIds = vehicleIds;
    } else if (typeof vehicleIds === 'string') {
        form.vehicleIds = [vehicleIds];
    } else {
        form.vehicleIds = [];
    }

    const metaTripId = post.value.metaInfos['rtb:trip_id'];
    if (typeof metaTripId === 'string') {
        form.metaTripId = metaTripId;
    } else {
        form.metaTripId = null;
    }

    const travelRole = post.value.metaInfos['rtb:travel_role'];
    if (typeof travelRole === 'string') {
        form.travelRole = travelRole as TravelRole;
    } else {
        form.travelRole = null;
    }

    subtitle.value = `${getBaseText(post.value)} (${prettyDates(post.value)})`;
    fullTitle.value = `${fullTitle.value} · ${subtitle.value}`;
}

fetchPost();
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
                    :show-vehicle-id="isTransportPost(post)"
                    :vehicle-ids="form.vehicleIds"
                    :travel-role="form.travelRole"
                    :meta-trip-id="form.metaTripId"
                    @select-travel-reason="
                        (reason) => (form.travelReason = reason)
                    "
                    @cancel="goBack"
                    @select-visibility="(vis) => (form.visibility = vis)"
                    @update:tags="(tags) => (form.tags = tags)"
                    @update:vehicle-ids="(ids) => (form.vehicleIds = ids)"
                    @update:travel-role="(role) => (form.travelRole = role)"
                    @update:trip-id="(ids) => (form.metaTripId = ids)"
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
