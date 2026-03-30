<script setup lang="ts">
import { api } from '@/api';
import Loading from '@/Components/Loading.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StopoversListEntry from '@/Pages/NewPostDialog/Partials/StopoversListEntry.vue';
import router from '@/router';
import { getEmoji } from '@/Services/DepartureTypeService';
import { normalizeQueryParam } from '@/Services/QueryParamService';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import {
    MotisTripDto,
    StopPlaceDto,
    TransportPostExitUpdateRequest,
} from '../../../types/Api.gen';

const { t } = useI18n();
const vueRouter = useRouter();
const route = useRoute();

const tripId = ref<string>('');
const startId = ref<string>('');
const startTime = ref<string>('');
const postId = ref<string>('');
const stopId = ref<string>('');

function updateUrlParams() {
    const queryParams = route.query;
    tripId.value = normalizeQueryParam(queryParams.tripId) || '';
    startId.value = normalizeQueryParam(queryParams.startId) || '';
    startTime.value = normalizeQueryParam(queryParams.startTime) || '';
    postId.value = normalizeQueryParam(queryParams.postId) || '';
    stopId.value = normalizeQueryParam(queryParams.stopId) || '';
    startTime.value = startTime.value.replaceAll(' ', '+');

    console.log(stopId.value);

    loadStopovers();
}

const trip = ref<MotisTripDto | null>(null);
const stopovers = ref<StopPlaceDto[]>([]);
const loading = ref(true);

async function loadStopovers() {
    loading.value = true;
    try {
        const response = await api.locations.stopovers({
            tripId: tripId.value,
            startId: startId.value,
            startTime: startTime.value,
        });
        trip.value = response.data.trip;
        startTime.value = response.data.startTime;
        startId.value = response.data.startId;
        tripId.value = response.data.tripId;

        const filterTime = startTime.value
            ? DateTime.fromISO(startTime.value)
            : null;

        stopovers.value = trip.value?.legs[0]
            ? [...trip.value.legs[0].intermediateStops, trip.value.legs[0].to]
            : [];

        stopovers.value = stopovers.value.filter((stopover) => {
            const time =
                stopover.scheduledDeparture || stopover.scheduledArrival;
            const luxonTime = time ? DateTime.fromISO(time) : null;

            if (filterTime && luxonTime) {
                return luxonTime >= filterTime;
            }
            return true;
        });
    } catch (error) {
        console.error('Error loading stopovers:', error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    updateUrlParams();
});

function submit(stopover: StopPlaceDto) {
    if (postId.value && postId.value.length > 0) {
        api.posts
            .updateTransportPostExit(postId.value, {
                stopId: stopover.tripStopId,
            } as TransportPostExitUpdateRequest)
            .then(() => {
                vueRouter.push(`/posts/${postId.value}`);
            });
        return;
    }

    redirectCreatePost(stopover);
}

function redirectCreatePost(stopover: StopPlaceDto) {
    const params: Record<string, string | undefined> = {
        tripId: tripId.value,
        startId: startId.value,
        startTime: startTime.value,
        stopId: stopover.stopId,
        stopTime:
            stopover.scheduledDeparture ||
            stopover.scheduledArrival ||
            undefined,
        stopName: stopover.name,
        stopMode: trip.value?.legs[0].mode,
        lineName:
            trip.value?.legs[0].displayName ||
            trip.value?.legs[0].routeShortName,
    };
    router.push({
        path: '/posts/transport/create',
        query: params,
    });
}

function getTitle() {
    let title = trip.value?.legs[0].mode
        ? getEmoji(trip.value?.legs[0].mode)
        : '';
    title =
        title +
        ' ' +
        ((trip.value?.legs[0].displayName ||
            trip.value?.legs[0].routeShortName) ??
            '');
    return title + ' ➜ ' + trip.value?.legs[0].headSign;
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('new_post.select_exit') }}
            </h2>
        </template>
        <Loading v-if="loading" class="mx-auto my-4"></Loading>
        <div v-else class="card bg-base-100 mb-10 min-w-full shadow-md">
            <!-- Results -->
            <ul class="list">
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    {{ getTitle() }}
                </li>
                <StopoversListEntry
                    v-for="(stopover, index) in stopovers"
                    :key="index"
                    :stop="stopover"
                    :mode="trip?.legs[0].mode"
                    :short-name="trip?.legs[0].routeShortName"
                    :real-time="trip?.legs[0].realTime"
                    :selected="stopId == stopover.tripStopId"
                    @click="submit(stopover)"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
