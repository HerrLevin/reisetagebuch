<script setup lang="ts">
import { api } from '@/api';
import Loading from '@/Components/Loading.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TransitousSearch from '@/Pages/NewPostDialog/Partials/TransitousSearch.vue';
import AirportSearch from '@/Pages/NewRoute/Partials/AirportSearch.vue';
import TripDetailsForm from '@/Pages/Trips/Partials/TripDetailsForm.vue';
import { AutocompleteResponse } from '@/types/motis';
import {
    CreateTripForm,
    FormStops,
    ProviderKey,
    Providers,
    TripLocation,
} from '@/types/TripCreation';
import { Head } from '@inertiajs/vue3';
import { PlaneTakeoff, TrainFront } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { StoreTripRequest, TransportMode } from '../../../types/Api.gen';

const { t } = useI18n();

const provider = ref<ProviderKey>('transitous');

const providers: Providers = {
    transitous: {
        name: 'Transitous',
        icon: TrainFront,
    },
    airports: {
        name: t('new_route.airports'),
        icon: PlaneTakeoff,
    },
};

const loading = ref(false);

const model = ref<CreateTripForm>({
    startLocation: null,
    endLocation: null,
    departureTime: DateTime.now(),
    arrivalTime: DateTime.now().plus({ hours: 1 }),
    transportMode: null,
    lineName: '',
    tripShortName: '',
    stops: [],
    routeColor: null,
    routeTextColor: null,
});

const form = reactive({
    mode: TransportMode.BUS,
    lineName: '',
    routeLongName: '',
    tripShortName: '',
    displayName: '',
    origin: '',
    originType: 'identifier',
    destination: '',
    destinationType: 'identifier',
    departureTime: '',
    arrivalTime: '',
    stops: [] as FormStops[],
} as StoreTripRequest);

function selectLocation(
    e: AutocompleteResponse,
    type: 'start' | 'end' | TripLocation,
) {
    const location: TripLocation = {
        name: e.name,
        id: e.id,
        identifier: e.identifier,
        latitude: e.lat,
        longitude: e.lon,
    };
    if (type === 'start') {
        model.value.startLocation = location;
    } else if (type === 'end') {
        model.value.endLocation = location;
    } else {
        // update existing stop
        const index = model.value.stops.findIndex(
            (s) => s.order === type.order,
        );
        if (index !== -1) {
            model.value.stops[index] = { ...location, order: type.order };
        } else {
            model.value.stops.push({
                ...location,
                order: model.value.stops.length + 1,
            });
        }
        // sort stops by order
        model.value.stops.sort((a, b) => (a.order! < b.order! ? -1 : 1));
    }
}

function addStop() {
    const newStop: TripLocation = {
        order: model.value.stops.length + 1,
        name: '',
        identifier: '',
        latitude: 0,
        longitude: 0,
    };
    model.value.stops.push(newStop);
}

function submit() {
    if (!model.value.startLocation || !model.value.endLocation) {
        alert(t('new_route.alerts.both_locations_required'));
        return;
    }
    if (!model.value.departureTime || !model.value.arrivalTime) {
        alert(t('new_route.alerts.both_times_required'));
        return;
    }
    if (!model.value.transportMode) {
        alert(t('new_route.alerts.transport_mode_required'));
        return;
    }

    const departure = model.value.departureTime.toISO();
    const arrival = model.value.arrivalTime.toISO();
    if (!departure || !arrival) {
        alert(t('new_route.alerts.invalid_date_time'));
        return;
    }

    if (model.value.arrivalTime <= model.value.departureTime) {
        alert(t('new_route.alerts.arrival_before_departure'));
        return;
    }

    loading.value = true;

    form.mode = model.value.transportMode;
    form.origin =
        model.value.startLocation.id || model.value.startLocation.identifier;
    form.originType = model.value.startLocation.id ? 'id' : 'identifier';
    form.destination =
        model.value.endLocation.id || model.value.endLocation.identifier;
    form.destinationType = model.value.endLocation.id ? 'id' : 'identifier';
    form.departureTime = departure;
    form.arrivalTime = arrival;
    form.lineName = model.value.lineName || '';
    form.tripShortName = model.value.tripShortName || '';
    model.value.stops = model.value.stops.filter(
        (stop) => stop.identifier && stop.name,
    );
    form.stops = model.value.stops.map((stop, index) => ({
        order: index + 1,
        identifier: stop.id || stop.identifier,
        identifierType: stop.id ? 'id' : 'identifier',
    }));

    api.trips
        .storeTrip(form)
        .then((response) => {
            // Reset the form after successful submission
            model.value = {
                startLocation: null,
                endLocation: null,
                departureTime: DateTime.now(),
                arrivalTime: DateTime.now().plus({ hours: 1 }),
                transportMode: null,
                lineName: '',
                tripShortName: '',
                stops: [],
                routeColor: null,
                routeTextColor: null,
            };
            window.location.href = route('posts.create.stopovers', {
                tripId: response.data.tripId,
                startId: response.data.startId,
                startTime: response.data.startTime,
            });
            loading.value = false;
        })
        .catch((response) => {
            loading.value = false;
            if (response.status === 422) {
                alert(response.data.message || 'Failed to create trip');
            }
        })
        .catch((error) => {
            loading.value = false;
            if (error.response && error.response.status === 422) {
                alert(
                    'Validation error: ' +
                        JSON.stringify(error.response.data.errors),
                );
            } else {
                alert(
                    'An error occurred: ' +
                        (error.response?.data?.message || error.message),
                );
            }
        });
}

function blur() {
    const activeElement = document.activeElement as HTMLElement;
    if (activeElement) {
        activeElement.blur();
    }
}

watch(
    () => provider.value,
    () => {
        // Reset model when provider changes
        model.value = {
            startLocation: null,
            endLocation: null,
            departureTime: DateTime.now(),
            arrivalTime: DateTime.now().plus({ hours: 1 }),
            transportMode: null,
            lineName: '',
            tripShortName: '',
            stops: [],
            routeColor: null,
            routeTextColor: null,
        };
    },
);
</script>

<template>
    <Head :title="t('new_route.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('new_route.title') }}
            </h2>
        </template>

        <div class="flex justify-end gap-4 px-6 pb-4">
            <div class="dropdown">
                <div
                    tabindex="0"
                    role="button"
                    class="btn btn-outline btn-primary"
                >
                    <component
                        :is="providers[provider].icon"
                        class="inline size-4"
                    />
                    {{ providers[provider].name }}
                </div>
                <ul
                    tabindex="-1"
                    class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
                >
                    <li v-for="(prov, key) in providers" :key="key">
                        <a
                            @click="
                                provider = key;
                                blur();
                            "
                        >
                            <component :is="prov.icon" class="inline size-4" />
                            {{ prov.name }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <div class="card-body">
                <div class="grid grid-cols-1 gap-8">
                    <TransitousSearch
                        v-if="provider === 'transitous'"
                        @select="selectLocation($event, 'start')"
                    />
                    <AirportSearch
                        v-else
                        @select="selectLocation($event, 'start')"
                    />
                </div>
                <div
                    v-for="stop in model.stops"
                    :key="stop.order"
                    class="grid grid-cols-1 gap-8"
                >
                    <TransitousSearch @select="selectLocation($event, stop)" />
                </div>
                <div v-if="provider === 'transitous'">
                    <a class="link" @click.prevent="addStop()">
                        {{ t('new_route.add_stop') }}
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-8">
                    <TransitousSearch
                        v-if="provider === 'transitous'"
                        @select="selectLocation($event, 'end')"
                    />
                    <AirportSearch
                        v-else
                        @select="selectLocation($event, 'end')"
                    />
                </div>
            </div>
        </div>
        <div class="card bg-base-100 mt-4 min-w-full p-0 shadow-md">
            <div class="card-body">
                <div class="card-title">
                    {{ t('new_route.route_details') }}
                </div>
                <TripDetailsForm v-model="model" />
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <Loading v-if="loading" class="me-2" />
            <button class="btn btn-primary" :disabled="loading" @click="submit">
                {{ t('new_route.create_route') }}
            </button>
        </div>
    </AuthenticatedLayout>
</template>
