<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostCreationForm from '@/Pages/NewPostDialog/Partials/PostCreationForm.vue';
import { getEmoji } from '@/Services/DepartureTypeService';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import {
    TransportMode,
    TravelReason,
    Visibility,
} from '../../../types/Api.gen';

const { t } = useI18n();
const vueRouter = useRouter();
const route = useRoute();

useTitle(t('new_post.title'));

const mode = route.query.stopMode as string | undefined;
const name = (route.query.stopName as string | undefined) || '';
const line = (route.query.lineName as string | undefined) || '';
const title = line ? `${line} ➜ ${name}` : name;
let emoji = '';

if (mode) {
    emoji = getEmoji(mode as TransportMode);
}

const tripId = route.query.tripId as string | undefined;
const startId = route.query.startId as string | undefined;
const startTime = route.query.startTime as string | undefined;
const stopId = route.query.stopId as string | undefined;
const stopTime = route.query.stopTime as string | undefined;
const loading = ref(false);

const form = reactive({
    body: '',
    tripId: tripId,
    startId: startId,
    startTime: startTime,
    stopId: stopId,
    stopTime: stopTime,
    visibility: Visibility.Public,
    tags: [] as string[],
    travelReason: TravelReason.Leisure,
    vehicleIds: [] as string[],
    metaTripId: null,
    travelRole: null,
});

function submitForm() {
    loading.value = true;
    api.posts
        .storeTransportPost(form)
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
                    :name="title"
                    :emoji="emoji"
                    :show-travel-reason="true"
                    :show-vehicle-id="true"
                    :vehicle-ids="form.vehicleIds"
                    :loading="loading"
                    @cancel="vueRouter.back()"
                    @select-travel-reason="
                        (travelReason) => (form.travelReason = travelReason)
                    "
                    @select-visibility="
                        (visibility) => (form.visibility = visibility)
                    "
                    @update:tags="(tags) => (form.tags = tags)"
                    @update:vehicle-ids="(ids) => (form.vehicleIds = ids)"
                    @update:travel-role="(role) => (form.travelRole = role)"
                    @update:trip-id="
                        (metaTripId) => (form.metaTripId = metaTripId)
                    "
                />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
