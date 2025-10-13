<script setup lang="ts">
import LocationHistoryMap from '@/Components/Maps/LocationHistoryMap.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { LocationHistoryDto, TripHistoryEntryDto } from '@/types';
import { DateTime } from 'luxon';
import { ref } from 'vue';

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

function selectDate(newValue: string) {
    if (newValue) {
        window.location.href = route('location-history.index', {
            when: newValue,
        });
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                Location History
            </h2>
        </template>

        <div class="min-w-full space-y-6">
            <div class="card bg-base-100 min-w-full p-8 shadow-md">
                <div class="mt-4">
                    <label for="when" class="block text-sm font-medium">
                        Date for Location History
                    </label>
                    <input
                        id="when"
                        :value="selectedDate?.toISODate()"
                        type="date"
                        class="input input-bordered mt-1 block w-full"
                        @change="selectDate($event?.target?.value || '')"
                    />
                    <p class="mt-2 text-sm text-gray-500">
                        Select a date and time to view the location history.
                    </p>
                </div>
            </div>
            <LocationHistoryMap
                v-if="locations.length > 0"
                :locations="locations"
                :trips="trips"
            />
            <div
                v-else
                class="card bg-base-100 min-w-full p-8 text-center shadow-md"
            >
                No location history available for the selected date.<br />
                <p class="mt-2">☹️</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
