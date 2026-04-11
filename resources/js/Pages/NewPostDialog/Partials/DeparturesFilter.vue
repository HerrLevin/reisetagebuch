<script setup lang="ts">
import TimeSelect from '@/Components/TimeSelect.vue';
import TransitousSearch from '@/Pages/NewPostDialog/Partials/TransitousSearch.vue';
import router from '@/router';
import {
    FilterGroups,
    getColor,
    getEmoji,
} from '@/Services/DepartureTypeService';
import { X } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { computed, PropType, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';
import { StopDto, TransportMode } from '../../../../types/Api.gen';

const { t } = useI18n();

const departuresPath = '/posts/transport/departures';
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

function evaluateProps() {
    if (props.requestTime) {
        selectedTime.value = DateTime.fromISO(props.requestTime);
    } else {
        selectedTime.value = DateTime.now();
    }
}

function submitTypeahead(submittedIdentifier: string | null) {
    if (submittedIdentifier) {
        router.push({
            path: departuresPath,
            query: {
                latitude: String(props.latitude),
                longitude: String(props.longitude),
                identifier: submittedIdentifier,
                when: selectedTime.value?.toISO() ?? undefined,
            },
        });
    }
}

function updateTime() {
    router.push({
        path: departuresPath,
        query: {
            latitude: String(props.latitude),
            longitude: String(props.longitude),
            identifier: identifier.value,
            when: selectedTime.value?.toISO() ?? undefined,
        },
    });
}

const identifier = computed(() => {
    return props.requestIdentifier || props.location?.id;
});
watch(
    () => props.requestTime,
    () => {
        evaluateProps();
    },
    { immediate: true },
);
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
                <TimeSelect
                    v-model="selectedTime"
                    @update:model-value="updateTime()"
                />
            </li>
            <li
                class="flex gap-1 overflow-x-scroll p-4 pb-2 text-xs tracking-wide"
            >
                <RouterLink
                    v-if="filter.length > 0"
                    class="btn btn-sm btn-neutral"
                    :to="{
                        path: departuresPath,
                        query: {
                            latitude: String(latitude),
                            longitude: String(longitude),
                            identifier: identifier,
                        },
                    }"
                >
                    <X class="h-4 w-4" />
                </RouterLink>

                <RouterLink
                    v-for="(mode, name) in FilterGroups"
                    :key="name"
                    class="btn btn-sm text-white"
                    :class="{
                        'opacity-60':
                            filter.length > 0 && mode[0] !== filter[0],
                    }"
                    :to="{
                        path: departuresPath,
                        query: {
                            latitude: String(latitude),
                            longitude: String(longitude),
                            filter: mode.join(','),
                            identifier: identifier,
                            when: selectedTime?.toISO() ?? undefined,
                        },
                    }"
                    :style="`background-color: ${getColor(mode[0])}`"
                >
                    {{ getEmoji(mode[0]) }}

                    {{ t(`transport_group.${name}`) }}
                </RouterLink>
            </li>
        </ul>
    </div>
</template>
