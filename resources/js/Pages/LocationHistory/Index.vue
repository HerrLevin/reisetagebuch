<script setup lang="ts">
import { api } from '@/app';
import Loading from '@/Components/Loading.vue';
import LocationHistoryMap from '@/Components/Maps/LocationHistoryMap.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    LocationHistoryEntryDto,
    TripHistoryEntryDto,
} from '../../../types/Api.gen';

const { t } = useI18n();

const selectedDate = ref<DateTime>(DateTime.now());
const locations = ref<LocationHistoryEntryDto[]>([]);
const trips = ref<TripHistoryEntryDto[]>([]);
const loading = ref(true);

async function fetchData() {
    loading.value = true;
    try {
        const response = await api.locations.locationHistory({
            when: selectedDate.value?.toISODate() || undefined,
        });
        locations.value = response.data.locations;
        trips.value = response.data.trips;
    } catch (error) {
        console.error('Error loading location history:', error);
    } finally {
        loading.value = false;
    }
}

function countWaypoints() {
    const waypoints = locations.value.filter(
        (location) => location.name === null,
    );

    return waypoints.length;
}

function selectDate(newValue: string) {
    if (newValue) {
        selectedDate.value = DateTime.fromISO(newValue);
        fetchData();
    }
}

function previousDay() {
    selectedDate.value = selectedDate.value.minus({ days: 1 });
    fetchData();
}

function nextDay() {
    selectedDate.value = selectedDate.value.plus({ days: 1 });
    fetchData();
}

function today() {
    selectedDate.value = DateTime.now();
    fetchData();
}

fetchData();
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('pages.location_history.title') }}
            </h2>
        </template>

        <div class="min-w-full space-y-6">
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <div class="mt-4">
                    <label for="when" class="block text-sm font-medium">
                        {{ t('pages.location_history.date') }}
                    </label>
                    <input
                        id="when"
                        :value="selectedDate?.toISODate()"
                        type="date"
                        class="input input-bordered mt-1 block w-full"
                        @change="selectDate($event?.target?.value || '')"
                    />
                    <p class="mt-2 text-sm text-gray-500">
                        {{ t('pages.location_history.date_help') }}
                    </p>
                </div>
                <div class="flex justify-between pt-4">
                    <button class="btn btn-primary" @click="previousDay()">
                        <ArrowLeft class="mr-1 inline size-4" />
                        <span class="sr-only sm:not-sr-only">
                            {{ t('pages.location_history.previous') }}
                        </span>
                    </button>
                    <button class="btn btn-primary" @click="today()">
                        {{ t('pages.location_history.today') }}
                    </button>
                    <button class="btn btn-primary" @click="nextDay()">
                        <span class="sr-only sm:not-sr-only">
                            {{ t('pages.location_history.next') }}
                        </span>
                        <ArrowRight class="ml-1 inline size-4" />
                    </button>
                </div>
            </div>
            <Loading v-if="loading" class="mx-auto my-4"></Loading>
            <template v-else-if="locations.length > 0 || trips.length > 0">
                <LocationHistoryMap :locations="locations" :trips="trips" />
                <div
                    class="card bg-base-100 min-w-full p-8 text-center shadow-md"
                >
                    <div class="mt-4 text-sm text-gray-500">
                        {{ t('pages.location_history.showing_for') }}
                        <strong>{{
                            selectedDate
                                ? selectedDate.toLocaleString(
                                      DateTime.DATE_FULL,
                                  )
                                : t('pages.location_history.all_time')
                        }}</strong
                        >.
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        {{
                            t(
                                'pages.location_history.waypoints',
                                countWaypoints(),
                                { count: locations.length - countWaypoints() },
                            )
                        }}
                        <br />
                        {{
                            t(
                                'pages.location_history.location_entries',
                                locations.length - countWaypoints(),
                                { count: locations.length - countWaypoints() },
                            )
                        }}
                        <br />
                        {{
                            t(
                                'pages.location_history.trip_entries',
                                trips.length,
                                { count: trips.length },
                            )
                        }}
                    </div>
                </div>
            </template>
            <div
                v-else
                class="card bg-base-100 min-w-full p-8 text-center shadow-md"
            >
                {{ t('pages.location_history.no_history') }}<br />
                <p class="mt-2">☹️</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
