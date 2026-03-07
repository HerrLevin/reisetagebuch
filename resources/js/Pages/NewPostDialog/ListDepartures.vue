<script setup lang="ts">
import { api } from '@/api';
import Loading from '@/Components/Loading.vue';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeparturesFilter from '@/Pages/NewPostDialog/Partials/DeparturesFilter.vue';
import DeparturesListEntry from '@/Pages/NewPostDialog/Partials/DeparturesListEntry.vue';
import { LocationService } from '@/Services/LocationService';
import { normalizeQueryParam } from '@/Services/QueryParamService';
import { useUserStore } from '@/stores/user';
import { TriangleAlert } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import {
    DeparturesDto,
    MotisStopDto,
    TransportMode,
} from '../../../types/Api.gen';

const { t } = useI18n();

useTitle(t('new_post.title'));

const user = useUserStore();
const route = useRoute();

const requestIdentifier = ref<string | undefined>(undefined);
const latitude = ref<number>(0);
const longitude = ref<number>(0);
const requestTime = ref<string>('');
const modes = ref<TransportMode[]>([]);

const departures = ref<DeparturesDto | null>(null);
const stop = ref<MotisStopDto | null>(null);
const loading = ref(true);
const time = ref<DateTime | null>(null);

const intervalId = ref<number | null>(null);

function updateQueryParams() {
    const urlParams = route.query;
    requestIdentifier.value = normalizeQueryParam(urlParams.identifier);
    latitude.value = Number.parseFloat(
        normalizeQueryParam(urlParams.latitude) || '0',
    );
    longitude.value = Number.parseFloat(
        normalizeQueryParam(urlParams.longitude) || '0',
    );
    requestTime.value = normalizeQueryParam(urlParams.when) || '';
    modes.value = urlParams.filter
        ? (normalizeQueryParam(urlParams.filter)!.split(',') as TransportMode[])
        : [];

    loadDepartures();
}

async function loadDepartures() {
    loading.value = true;
    try {
        const response = await api.locations.departures({
            latitude: latitude.value,
            longitude: longitude.value,
            identifier: requestIdentifier.value || undefined,
            when: requestTime.value || undefined,
            modes: modes.value.length > 0 ? modes.value : undefined,
        });
        departures.value = response.data.departures;
        modes.value = response.data.modes;
        requestTime.value = response.data.requestTime;
        stop.value =
            requestIdentifier.value === null
                ? null
                : response.data.departures?.stop;
        latitude.value = response.data.requestLatitude;
        longitude.value = response.data.requestLongitude;
        time.value = DateTime.fromISO(response.data.requestTime);
    } catch {
        departures.value = null;
        loading.value = false;
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    updateLocation();
    updateQueryParams();
    intervalId.value = setInterval(updateLocation, 60 * 1000);
});

watch(
    () => route.query,
    () => {
        updateQueryParams();
    },
);

onUnmounted(() => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
    }
});

function updateLocation() {
    LocationService.getPosition(!!user.user)
        .then((position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
        })
        .catch(() => {});
}

const currentRoute = useRoute();
const showStartButton = ref(false);
showStartButton.value = currentRoute.name === 'posts.create.start';
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('new_post.title') }}
            </h2>
        </template>
        <DeparturesFilter
            :latitude="latitude"
            :longitude="longitude"
            :filter="modes"
            :request-time="requestTime"
            :request-identifier="requestIdentifier"
            :location="stop"
        />
        <Loading v-if="loading" class="mx-auto my-4"></Loading>
        <div v-else class="card bg-base-100 min-w-full shadow-md">
            <!-- Results -->
            <ul class="list">
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    {{
                        t('new_post.departures_at_stop', {
                            location: departures?.stop.name || 'unknown',
                            time: time
                                ? time?.hasSame(DateTime.now(), 'day')
                                    ? time.toLocaleString(DateTime.TIME_SIMPLE)
                                    : time.toLocaleString(DateTime.DATETIME_MED)
                                : 'unknown',
                        })
                    }}
                </li>
                <DeparturesListEntry
                    v-for="(departure, index) in departures?.departures"
                    :key="index"
                    :stop-time="departure"
                    :show-start-button="showStartButton"
                    :stop="departures!.stop"
                />
            </ul>
            <div
                v-if="departures === null && !loading"
                role="alert"
                class="alert alert-warning m-4 shadow-lg"
            >
                <TriangleAlert class="h-6 w-6 shrink-0 stroke-current" />
                <span>{{ t('new_post.no_departures') }}</span>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
