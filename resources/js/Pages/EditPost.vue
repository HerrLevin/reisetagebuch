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
    vehicleIds: [] as string[],
    metaTripId: null as string | null,
    travelRole: null as string | null,
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

const vehicleIds = props.post.metaInfos['rtb:vehicle_id'];
if (Array.isArray(vehicleIds)) {
    form.vehicleIds = vehicleIds;
} else if (typeof vehicleIds === 'string') {
    form.vehicleIds = [vehicleIds];
} else {
    form.vehicleIds = [];
}

const metaTripId = props.post.metaInfos['rtb:trip_id'];
if (typeof metaTripId === 'string') {
    form.metaTripId = metaTripId;
} else {
    form.metaTripId = null;
}

const travelRole = props.post.metaInfos['rtb:travel_role'];
if (typeof travelRole === 'string') {
    form.travelRole = travelRole;
} else {
    form.travelRole = null;
}

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
