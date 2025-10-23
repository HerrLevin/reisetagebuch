<script setup lang="ts">
import LocationHistoryMap from '@/Components/Maps/LocationHistoryMap.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { LocationHistoryDto, TripHistoryEntryDto } from '@/types';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    locations: {
        type: Array as () => LocationHistoryDto[],
        required: true,
    },
    trips: {
        type: Array as () => TripHistoryEntryDto[],
        required: true,
    },
    when: {
        type: String,
        default: '',
    },
});

const selectedDate = ref<DateTime | null>();

selectedDate.value = props.when ? DateTime.fromISO(props.when) : null;

function countWaypoints() {
    const waypoints = props.locations.filter(
        (location) => location.name === null,
    );

    return waypoints.length;
}

function selectDate(newValue: string) {
    if (newValue) {
        window.location.href = route('location-history.index', {
            when: newValue,
        });
    }
}

function previousDay() {
    if (selectedDate.value) {
        const previous = selectedDate.value.minus({ days: 1 });
        selectDate(previous.toISODate() || '');
    } else {
        const previous = DateTime.now().minus({ days: 1 });
        selectDate(previous.toISODate() || '');
    }
}

function nextDay() {
    if (selectedDate.value) {
        const next = selectedDate.value.plus({ days: 1 });
        selectDate(next.toISODate() || '');
    } else {
        const next = DateTime.now().plus({ days: 1 });
        selectDate(next.toISODate() || '');
    }
}

function today() {
    window.location.href = route('location-history.index');
}
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
            <template v-if="locations.length > 0 || trips.length > 0">
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
