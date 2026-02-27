<script setup lang="ts">
import TransitousSearch from '@/Pages/NewPostDialog/Partials/TransitousSearch.vue';
import {
    FilterGroups,
    getColor,
    getEmoji,
} from '@/Services/DepartureTypeService';
import { StopDto } from '@/types';
import { TransportMode } from '@/types/enums';
import { Link } from '@inertiajs/vue3';
import { Clock, X } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { computed, PropType, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    filter: {
        type: Array as PropType<TransportMode[]>,
        default: () => [],
    },
    requestTime: {
        type: String,
        default: '',
    },
    requestIdentifier: {
        type: String,
        nullable: true,
        default: null,
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

function submitTypeahead(submittedIdentifier: string | null) {
    if (submittedIdentifier) {
        window.location.href = route('posts.create.departures', {
            latitude: props.latitude,
            longitude: props.longitude,
            identifier: submittedIdentifier,
            when: selectedTime.value?.toISO(),
        });
    }
}

const identifier = computed(() => {
    return props.requestIdentifier || props.location?.stopId;
});
</script>

<template>
    <!-- Filters -->
    <div class="card bg-base-100 mb-4 min-w-full shadow-md">
        <ul class="list">
            <li class="list-row">
                <div class="list-col-grow">
                    <TransitousSearch
                        :stop="props.location"
                        :latitude="props.latitude"
                        :longitude="props.longitude"
                        @select-identifier="submitTypeahead"
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
                            {{ t('new_post.departures_filter.now') }}
                        </button>
                        <button
                            class="btn btn-secondary w-full"
                            @click="selectedTime = null"
                        >
                            {{ t('new_post.departures_filter.clear') }}
                        </button>
                    </div>
                    <Link
                        class="btn btn-primary mt-2 w-full"
                        :href="
                            route('posts.create.departures', {
                                latitude: latitude,
                                longitude: longitude,
                                when: selectedTime?.toISO(),
                                identifier: identifier,
                                filter: filter.join(','),
                            })
                        "
                    >
                        {{ t('new_post.departures_filter.set_datetime') }}
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
                            identifier: identifier,
                        })
                    "
                >
                    <X class="h-4 w-4" />
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
                            identifier: identifier,
                            when: selectedTime?.toISO(),
                        })
                    "
                    :style="`background-color: ${getColor(mode[0])}`"
                >
                    {{ getEmoji(mode[0]) }}

                    {{ t(`transport_group.${name}`) }}
                </Link>
            </li>
        </ul>
    </div>
</template>
