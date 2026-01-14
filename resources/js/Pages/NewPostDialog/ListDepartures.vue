<script setup lang="ts">
import Loading from '@/Components/Loading.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeparturesFilter from '@/Pages/NewPostDialog/Partials/DeparturesFilter.vue';
import DeparturesListEntry from '@/Pages/NewPostDialog/Partials/DeparturesListEntry.vue';
import { LocationService } from '@/Services/LocationService';
import { DeparturesDto, StopDto } from '@/types';
import { TransportMode } from '@/types/enums';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { DateTime } from 'luxon';
import { onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const urlParams = new URLSearchParams(window.location.search);
const requestIdentifier = ref<string | null>(urlParams.get('identifier'));
const requestLatitude = ref<number>(
    Number.parseFloat(urlParams.get('latitude') || '0'),
);
const requestLongitude = ref<number>(
    Number.parseFloat(urlParams.get('longitude') || '0'),
);
const requestTime = ref<string>(urlParams.get('when') || '');
const filter = ref<TransportMode[]>(
    urlParams.get('filter')
        ? (urlParams.get('filter')!.split(',') as TransportMode[])
        : [],
);

const departures = ref<DeparturesDto | null>(null);
const stop = ref<StopDto | null>(null);
const loading = ref(true);
const time = ref<DateTime | null>(null);

const latitude = ref(requestLatitude.value);
const longitude = ref(requestLongitude.value);

const user = usePage().props.auth.user ?? null;
const intervalId = ref<number | null>(null);

async function loadDepartures() {
    loading.value = true;
    try {
        const response = await axios.get('/api/locations/departures', {
            params: {
                latitude: latitude.value,
                longitude: longitude.value,
                identifier: requestIdentifier.value,
                when: requestTime.value || undefined,
                filter:
                    filter.value.length > 0
                        ? filter.value.join(',')
                        : undefined,
            },
        });
        departures.value = response.data.departures;
        filter.value = response.data.filter;
        requestTime.value = response.data.requestTime;
        stop.value =
            requestIdentifier.value === null
                ? null
                : response.data.departures?.stop;
        requestLatitude.value = response.data.requestLatitude;
        requestLongitude.value = response.data.requestLongitude;
        time.value = DateTime.fromISO(response.data.requestTime);
    } catch (error) {
        console.error('Error loading departures:', error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    updateLocation();
    intervalId.value = setInterval(updateLocation, 60 * 1000);
    loadDepartures();
});

onUnmounted(() => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
    }
});

function updateLocation() {
    LocationService.getPosition(!!user)
        .then((position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
        })
        .catch(() => {});
}

const showStartButton = ref(false);
showStartButton.value = route().current('posts.create.start');
</script>

<template>
    <Head :title="`${departures?.stop.name}: departures`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('new_post.title') }}
            </h2>
        </template>
        <DeparturesFilter
            :latitude="latitude"
            :longitude="longitude"
            :filter="filter"
            :request-time="requestTime"
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
        </div>
    </AuthenticatedLayout>
</template>
