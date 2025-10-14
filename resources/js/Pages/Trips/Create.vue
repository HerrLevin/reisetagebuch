<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AirportSearch from '@/Pages/NewPostDialog/Partials/AirportSearch.vue';
import TransitousSearch from '@/Pages/NewPostDialog/Partials/TransitousSearch.vue';
import TripDetailsForm from '@/Pages/Trips/Partials/TripDetailsForm.vue';
import { AutocompleteResponse } from '@/types/motis';
import {
    CreateTripForm,
    FormStops,
    ProviderKey,
    Providers,
    TripLocation,
} from '@/types/TripCreation';
import { Head, useForm } from '@inertiajs/vue3';
import { PlaneTakeoff, TrainFront } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { ref, watch } from 'vue';

const provider = ref<ProviderKey>('transitous');

const providers: Providers = {
    transitous: {
        name: 'Transitous',
        icon: TrainFront,
    },
    airports: {
        name: 'Airports',
        icon: PlaneTakeoff,
    },
};

const model = ref<CreateTripForm>({
    startLocation: null,
    endLocation: null,
    departureTime: DateTime.now(),
    arrivalTime: DateTime.now().plus({ hours: 1 }),
    transportMode: null,
    lineName: '',
    tripShortName: '',
    stops: [],
});

const form = useForm({
    mode: '',
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
});

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
        alert('Please select both start and end locations.');
        return;
    }
    if (!model.value.departureTime || !model.value.arrivalTime) {
        alert('Please select both departure and arrival times.');
        return;
    }
    if (!model.value.transportMode) {
        alert('Please select a transport mode.');
        return;
    }

    const departure = model.value.departureTime.toISO();
    const arrival = model.value.arrivalTime.toISO();
    if (!departure || !arrival) {
        alert('Invalid date format.');
        return;
    }

    if (model.value.arrivalTime <= model.value.departureTime) {
        alert('Arrival time must be after departure time.');
        return;
    }

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

    form.post(route('trips.store'), {
        onSuccess: () => {
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
            };
            form.reset();
        },
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
        };
    },
);
</script>

<template>
    <Head title="New Trip" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Trip</h2>
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
                    <a class="link" @click.prevent="addStop()">Add Stopover</a>
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
                <div class="card-title">Trip Details</div>
                <TripDetailsForm v-model="model" />
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button class="btn btn-primary" @click="submit">Create Trip</button>
        </div>
    </AuthenticatedLayout>
</template>
