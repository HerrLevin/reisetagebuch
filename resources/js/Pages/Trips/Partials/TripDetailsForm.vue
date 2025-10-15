<script setup lang="ts">
import { getEmoji } from '@/Services/DepartureTypeService';
import { TransportMode } from '@/types/enums';
import { CreateTripForm } from '@/types/TripCreation';
import { DateTime } from 'luxon';

const model = defineModel<CreateTripForm>({
    required: true,
});

function selectDepartureDate(event: Event) {
    const target = event.target as HTMLInputElement;

    const date = DateTime.fromISO(target.value);
    if (model.value.departureTime) {
        model.value.departureTime = model.value.departureTime.set({
            year: date.year,
            month: date.month,
            day: date.day,
        });
    } else {
        model.value.departureTime = date;
    }
}

function selectDepartureTime(event: Event) {
    const target = event.target as HTMLInputElement;

    if (model.value.departureTime) {
        const [hours, minutes] = target.value.split(':').map(Number);
        model.value.departureTime = model.value.departureTime.set({
            hour: hours,
            minute: minutes,
        });
    } else {
        model.value.departureTime = DateTime.fromISO(target.value);
    }
}

function selectArrivalDate(event: Event) {
    const target = event.target as HTMLInputElement;

    const date = DateTime.fromISO(target.value);
    if (model.value.arrivalTime) {
        model.value.arrivalTime = model.value.arrivalTime.set({
            year: date.year,
            month: date.month,
            day: date.day,
        });
    } else {
        model.value.arrivalTime = date;
    }
}

function selectArrivalTime(event: Event) {
    const target = event.target as HTMLInputElement;

    if (model.value.arrivalTime) {
        const [hours, minutes] = target.value.split(':').map(Number);
        model.value.arrivalTime = model.value.arrivalTime.set({
            hour: hours,
            minute: minutes,
        });
    } else {
        model.value.arrivalTime = DateTime.fromISO(target.value);
    }
}
</script>

<template>
    <div class="grid grid-cols-1 gap-8">
        <div class="grid grid-cols-4 gap-4">
            <div class="col col-span-2 md:col-span-1">
                <label for="departureDate" class="font-bold">Departure</label>
                <input
                    id="departureDate"
                    type="date"
                    class="input input-bordered w-full"
                    :value="model.departureTime?.toFormat('yyyy-MM-dd')"
                    @change="selectDepartureDate"
                />
            </div>
            <div class="col col-span-2 md:col-span-1">
                <div class="flex justify-between opacity-60">
                    <label for="departureTime" class="font-bold"> Time </label>
                    <span class="text-sm">{{
                        model.departureTime?.zoneName
                    }}</span>
                </div>
                <input
                    id="departureTime"
                    type="time"
                    class="input input-bordered w-full"
                    :value="model.departureTime?.toFormat('HH:mm')"
                    @change="selectDepartureTime"
                />
            </div>
            <div class="col col-span-2 md:col-span-1">
                <label for="arrivalDate" class="font-bold">Arrival</label>
                <input
                    id="arrivalDate"
                    type="date"
                    class="input input-bordered w-full"
                    :value="model.arrivalTime?.toFormat('yyyy-MM-dd')"
                    @change="selectArrivalDate"
                />
            </div>
            <div class="col col-span-2 md:col-span-1">
                <div class="flex justify-between opacity-60">
                    <label for="departureTime" class="font-bold"> Time </label>
                    <span class="text-sm">
                        {{ model.arrivalTime?.zoneName }}
                    </span>
                </div>
                <input
                    id="departureTime"
                    type="time"
                    class="input input-bordered w-full"
                    :value="model.arrivalTime?.toFormat('HH:mm')"
                    @change="selectArrivalTime"
                />
            </div>
        </div>
        <div class="grid grid-cols-4 gap-4">
            <div class="col col-span-2 md:col-span-1">
                <label for="lineInput" class="font-bold">
                    Line <span class="font-light opacity-60">(e.g. RE 45)</span>
                </label>
                <input
                    id="lineInput"
                    v-model="model.lineName"
                    type="text"
                    class="input input-bordered w-full"
                    placeholder="Line Name"
                />
            </div>
            <div class="col col-span-2 md:col-span-1">
                <label for="vehicleInput" class="font-bold">
                    Code
                    <span class="font-light opacity-60">(e.g. 15123)</span>
                </label>
                <input
                    id="vehicleInput"
                    v-model="model.tripShortName"
                    type="text"
                    class="input input-bordered w-full"
                    placeholder="Code (e.g. 15123)"
                />
            </div>
            <div class="col col-span-2">
                <label for="categoryInput" class="font-bold">Travel Type</label>
                <select
                    id="categoryInput"
                    v-model="model.transportMode"
                    class="select select-bordered w-full"
                >
                    <option disabled selected>Select travel type</option>
                    <option
                        v-for="type in TransportMode"
                        :key="type"
                        :value="type"
                    >
                        {{ getEmoji(type) }}
                        {{
                            type.charAt(0).toUpperCase() +
                            type.slice(1).toLowerCase().replaceAll('_', ' ')
                        }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>
