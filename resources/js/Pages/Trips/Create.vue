<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TransitousSearch from '@/Pages/NewPostDialog/Partials/TransitousSearch.vue';
import TripDetailsForm from '@/Pages/Trips/Partials/TripDetailsForm.vue';
import { TransportMode } from '@/types/enums';
import { AutocompleteResponse } from '@/types/motis';
import { Head, useForm } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';

export type CreateTripForm = {
    startLocation: TripLocation | null;
    endLocation: TripLocation | null;
    departureTime: DateTime | null;
    arrivalTime: DateTime | null;
    transportMode: TransportMode | null;
    lineName: string | null;
    tripShortName: string | null;
    stops: TripLocation[];
};

export type TripLocation = {
    order?: number;
    name: string;
    identifier: string;
    latitude: number;
    longitude: number;
};

type FormStops = {
    order: number;
    identifier: string;
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
    destination: '',
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
        identifier: e.id,
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
    form.origin = model.value.startLocation.identifier;
    form.destination = model.value.endLocation.identifier;
    form.departureTime = departure;
    form.arrivalTime = arrival;
    form.lineName = model.value.lineName || '';
    form.tripShortName = model.value.tripShortName || '';
    model.value.stops = model.value.stops.filter(
        (stop) => stop.identifier && stop.name,
    );
    form.stops = model.value.stops.map((stop, index) => ({
        order: index + 1,
        identifier: stop.identifier,
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
</script>

<template>
    <Head title="New Trip" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">New Trip</h2>
        </template>

        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <div class="card-body">
                <div class="grid grid-cols-1 gap-8">
                    <TransitousSearch
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
                <div>
                    <a class="link" @click.prevent="addStop()">Add Stopover</a>
                </div>
                <div class="grid grid-cols-1 gap-8">
                    <TransitousSearch @select="selectLocation($event, 'end')" />
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
