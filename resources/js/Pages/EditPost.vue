<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { getBaseText, prettyDates } from '@/Services/PostTextService';
import { isLocationPost, isTransportPost } from '@/types/PostTypes';
import { reactive, ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import {
    BasePost,
    LocationPost,
    TransportPost,
    TravelReason,
    TravelRole,
    Visibility,
} from '../../types/Api.gen';

const { t } = useI18n();
const vueRouter = useRouter();

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
const loading = ref<boolean>(false);

function fetchPost() {
    api.posts
        .showPost(props.postId)
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
    visibility: Visibility.Public,
    tags: [] as string[],
    travelReason: TravelReason.Leisure,
    vehicleIds: [] as string[],
    metaTripId: null as string | null,
    travelRole: null as TravelRole | null,
});

function submitForm() {
    if (!post.value) {
        return;
    }
    loading.value = true;
    if (!form.body?.trim()) {
        form.body = undefined;
    }

    api.posts
        .updatePost(props.postId, form)
        .then((response) => {
            const postId = response.data.id;
            vueRouter.push(`/posts/${postId}`);
        })
        .finally(() => {
            loading.value = false;
        });
}

function prefillForm() {
    if (!post.value) {
        return;
    }

    form.id = post.value.id;
    form.body = post.value.body || '';
    form.visibility = post.value.visibility;
    form.tags = post.value.hashTags || [];
    form.travelReason =
        (post.value as LocationPost)?.travelReason || TravelReason.Leisure;

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

watchEffect(() => {
    if (fullTitle.value) {
        useTitle(fullTitle.value);
    }
});

fetchPost();
</script>

<template>
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
                    @cancel="vueRouter.back()"
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
