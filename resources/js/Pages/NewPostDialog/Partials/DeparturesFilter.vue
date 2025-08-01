<script setup lang="ts">
import Typeahead from '@/Components/Typeahead.vue';
import Clock from '@/Icons/Clock.vue';
import XMark from '@/Icons/XMark.vue';
import {
    FilterGroups,
    getColor,
    getEmoji,
} from '@/Services/DepartureTypeService';
import { Area, StopDto } from '@/types';
import { TransportMode } from '@/types/enums';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { DateTime } from 'luxon';
import { PropType, ref } from 'vue';
import { debounce } from 'vue-debounce';

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
    location: {
        type: Object as PropType<StopDto | null>,
        required: false,
        default: () => null,
    },
});

const search = ref('');
const suggestions = ref<
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    { label: string; value: any; subLabel: string | undefined }[]
>([]);

search.value = props.location?.name || '';

const modelChange = debounce(() => fetchSuggestions(), 300);

function fetchSuggestions() {
    if (search.value.length < 3) {
        suggestions.value = [];
        return;
    }

    const url = route('posts.create.geocode');
    axios
        .get(url, {
            params: {
                query: search.value,
                latitude: props.latitude,
                longitude: props.longitude,
            },
        })
        .then((response) => {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            suggestions.value = response.data.map((item: any) => ({
                label: item.name,
                value: item.id,
                subLabel: getArea(item.areas || []),
            }));
        })
        .catch((error) => {
            console.error('Error fetching suggestions:', error);
        });
}

function getArea(areas: Array<Area>) {
    if (areas.length === 0) {
        return '';
    }

    const defaultArea: undefined | Area = areas.find(
        (area: Area) => area.default,
    );
    const country: undefined | Area = areas.find(
        (area: Area) => area.adminLevel === 2,
    );

    if (defaultArea) {
        return country
            ? `${defaultArea.name}, ${country.name}`
            : defaultArea.name;
    }
}

const selectedTime = ref<DateTime | null>(null);
if (props.requestTime) {
    selectedTime.value = DateTime.fromISO(props.requestTime);
} else {
    selectedTime.value = DateTime.now();
}

function selectDate(date: EventTarget | null) {
    if (date && date instanceof HTMLInputElement) {
        const dateObject = DateTime.fromISO(date.value);
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
        const timeObject = DateTime.fromISO(time.value);
        selectedTime.value = selectedTime.value
            ? selectedTime.value.set({
                  hour: timeObject.hour,
                  minute: timeObject.minute,
              })
            : timeObject;
    }
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function submitTypeahead(test: any) {
    let identifier: string | undefined = undefined;
    if (test === undefined) {
        identifier = suggestions.value[0]?.value;
    }
    if (test?.value && typeof test.value === 'string') {
        identifier = test.value;
    }

    if (identifier) {
        window.location.href = route('posts.create.departures', {
            latitude: props.latitude,
            longitude: props.longitude,
            identifier: identifier,
            when: selectedTime.value?.toISO(),
        });
    }
}
</script>

<template>
    <!-- Filters -->
    <div class="card bg-base-100 mb-4 min-w-full shadow-md">
        <ul class="list">
            <li class="list-row">
                <div class="list-col-grow">
                    <Typeahead
                        v-model="search"
                        class="input input-bordered w-full"
                        name="departure-search"
                        :required="false"
                        :suggestions="suggestions"
                        @submit="submitTypeahead($event)"
                        @select="submitTypeahead($event)"
                        @focus="modelChange()"
                        @update:model-value="modelChange()"
                    />
                </div>
                <button
                    id="cally1"
                    popovertarget="datetime-picker"
                    class="btn btn-neutral"
                >
                    <Clock class="h-4 w-4" />
                </button>
                <div
                    id="datetime-picker"
                    popover
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
                                identifier: location?.stopId,
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
                            identifier: location?.stopId,
                        })
                    "
                >
                    <XMark class="h-4 w-4" />
                </Link>

                <Link
                    v-for="(mode, name) in FilterGroups"
                    :key="name"
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
                            identifier: location?.stopId,
                        })
                    "
                    :style="`background-color: ${getColor(mode[0])}`"
                >
                    {{ getEmoji(mode[0]) }}

                    {{ name }}
                </Link>
            </li>
        </ul>
    </div>
</template>

<style scoped></style>
