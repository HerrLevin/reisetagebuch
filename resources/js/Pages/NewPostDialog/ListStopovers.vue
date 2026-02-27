<script setup lang="ts">
import { api } from '@/api';
import Loading from '@/Components/Loading.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StopoversListEntry from '@/Pages/NewPostDialog/Partials/StopoversListEntry.vue';
import { getEmoji } from '@/Services/DepartureTypeService';
import { StopPlace, TripDto } from '@/types';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { TransportPostExitUpdateRequest } from '../../../types/Api.gen';

const { t } = useI18n();
const vueRouter = useRouter();

const urlParams = new URLSearchParams(window.location.search);
const tripId = ref<string>(urlParams.get('tripId') || '');
const startId = ref<string>(urlParams.get('startId') || '');
const startTime = ref<string>(urlParams.get('startTime') || '');
const postId = ref<string>(urlParams.get('postId') || '');

const trip = ref<TripDto | null>(null);
const stopovers = ref<StopPlace[]>([]);
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
    loadStopovers();
});

function submit(stopover: StopPlace) {
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

function redirectCreatePost(stopover: StopPlace) {
    const params = {
        tripId: tripId.value,
        startId: startId.value,
        startTime: startTime.value,
        stopId: stopover.stopId,
        stopTime: stopover.scheduledDeparture || stopover.scheduledArrival,
        stopName: stopover.name,
        stopMode: trip.value?.legs[0].mode,
        lineName:
            trip.value?.legs[0].displayName ||
            trip.value?.legs[0].routeShortName,
    };
    const searchParams = new URLSearchParams(params as Record<string, string>);
    window.location.href = `/posts/transport/create?${searchParams.toString()}`;
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
                    @click="submit(stopover)"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
