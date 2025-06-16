<script setup lang="ts">
import Typeahead from '@/Components/Typeahead.vue';
import Clock from '@/Icons/Clock.vue';
import XMark from '@/Icons/XMark.vue';
import {
    FilterGroups,
    getColor,
    getEmoji,
} from '@/Services/DepartureTypeService';
import { TransportMode } from '@/types/enums';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { PropType, ref, watch } from 'vue';

const props = defineProps({
    filter: {
        type: Array as PropType<TransportMode[]>,
        default: () => [],
    },
    requestTime: {
        type: String,
        default: '',
    },
    latitude: {
        type: Number,
        default: 0,
    },
    longitude: {
        type: Number,
        default: 0,
    },
});

const search = ref('');
const suggestions = ref<{ label: string; value: any }[]>([]);

const testStations = [
    'Karlsruhe Hbf',
    'Mannheim Hbf',
    'Stuttgart Hbf',
    'Freiburg Hbf',
];

watch(search, (newValue) => {
    if (newValue.length > 2) {
        suggestions.value = testStations
            .filter((station) =>
                station.toLowerCase().includes(newValue.toLowerCase()),
            )
            .map((station) => ({ label: station, value: station }));
    } else {
        suggestions.value = [];
    }
});

const selectedTime = ref<DateTime | null>(null);
if (props.requestTime) {
    selectedTime.value = DateTime.fromISO(props.requestTime);
} else {
    selectedTime.value = DateTime.now();
}

function selectDate(date: EventTarget | null) {
    if (date && date instanceof HTMLInputElement) {
        let dateObject = DateTime.fromISO(date.value);
        selectedTime.value = selectedTime.value
            ? selectedTime.value.set({
                  year: dateObject.year,
                  month: dateObject.month,
                  day: dateObject.day,
              })
            : dateObject;
    }
}

function selectTime(time: EventTarget | null) {
    if (time && time instanceof HTMLInputElement) {
        let timeObject = DateTime.fromISO(time.value);
        selectedTime.value = selectedTime.value
            ? selectedTime.value.set({
                  hour: timeObject.hour,
                  minute: timeObject.minute,
              })
            : timeObject;
    }
}

function test(test: any) {
    console.log('test', test);
}
</script>

<template>
    <!-- Filters -->
    <div class="card bg-base-100 mb-4 min-w-full shadow-md">
        <ul class="list">
            <li class="list-row">
                <div class="list-col-grow">
                    <Typeahead
                        class="input input-bordered w-full"
                        name="departure-search"
                        :required="false"
                        @submit="test($event)"
                        @select="test($event)"
                        v-model="search"
                        :suggestions="suggestions"
                    />
                </div>
                <button
                    popovertarget="datetime-picker"
                    class="btn btn-neutral"
                    id="cally1"
                >
                    <Clock class="h-4 w-4" />
                </button>
                <div
                    popover
                    id="datetime-picker"
                    class="dropdown bg-base-100 rounded-box p-3 shadow-lg"
                    style="position-anchor: --cally1"
                >
                    <input
                        type="date"
                        class="input input-bordered w-full"
                        :value="selectedTime?.toFormat('yyyy-MM-dd')"
                        @change="selectDate($event.target)"
                    />
                    <input
                        type="time"
                        class="input input-bordered mt-2 w-full"
                        :value="selectedTime?.toFormat('HH:mm')"
                        @change="selectTime($event.target)"
                    />
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <button
                            class="btn btn-secondary w-full"
                            @click="selectedTime = DateTime.now()"
                        >
                            Now
                        </button>
                        <button
                            class="btn btn-secondary w-full"
                            @click="selectedTime = null"
                        >
                            Clear
                        </button>
                    </div>
                    <Link
                        class="btn btn-primary mt-2 w-full"
                        :href="
                            route('posts.create.departures', {
                                latitude: latitude,
                                longitude: longitude,
                                when: selectedTime?.toISO(),
                            })
                        "
                    >
                        Apply
                    </Link>
                </div>
            </li>
            <li
                class="flex gap-1 overflow-x-scroll p-4 pb-2 text-xs tracking-wide"
            >
                <Link
                    v-if="filter.length > 0"
                    class="btn btn-sm btn-neutral"
                    :href="
                        route('posts.create.departures', {
                            latitude: latitude,
                            longitude: longitude,
                        })
                    "
                >
                    <XMark class="h-4 w-4" />
                </Link>

                <Link
                    class="btn btn-sm text-white"
                    :class="{
                        'opacity-60':
                            filter.length > 0 && mode[0] !== filter[0],
                    }"
                    :href="
                        route('posts.create.departures', {
                            latitude: latitude,
                            longitude: longitude,
                            filter: mode.join(','),
                        })
                    "
                    :style="`background-color: ${getColor(mode[0])}`"
                    v-for="(mode, name) in FilterGroups"
                    :key="name"
                >
                    {{ getEmoji(mode[0]) }}

                    {{ name }}
                </Link>
            </li>
        </ul>
    </div>
</template>

<style scoped></style>
