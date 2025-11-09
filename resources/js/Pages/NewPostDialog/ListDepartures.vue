<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeparturesFilter from '@/Pages/NewPostDialog/Partials/DeparturesFilter.vue';
import DeparturesListEntry from '@/Pages/NewPostDialog/Partials/DeparturesListEntry.vue';
import { LocationService } from '@/Services/LocationService';
import { DeparturesDto } from '@/types';
import { TransportMode } from '@/types/enums';
import { Head, usePage } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { onMounted, onUnmounted, PropType, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    departures: {
        type: Object as PropType<DeparturesDto> | null,
        required: false,
        default: () => null,
    },
    filter: {
        type: Array as PropType<TransportMode[]>,
        default: () => [],
    },
    requestTime: {
        type: String,
        default: '',
    },
    requestIdentifier: {
        type: String as PropType<string | null>,
        default: '',
    },
    requestLatitude: {
        type: Number,
        default: 0,
    },
    requestLongitude: {
        type: Number,
        default: 0,
    },
});

const time = ref(null as DateTime | null);
time.value = DateTime.fromISO(props.requestTime);

const latitude = ref(props.requestLatitude);
const longitude = ref(props.requestLongitude);

const user = usePage().props.auth.user ?? null;
const intervalId = ref<number | null>(null);
onMounted(() => {
    updateLocation();
    intervalId.value = setInterval(updateLocation, 60 * 1000);
});

onUnmounted(() => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
    }
});

function updateLocation() {
    LocationService.getPosition(!!user)
        .then((position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
        })
        .catch(() => {});
}

const showStartButton = ref(false);
showStartButton.value = route().current('posts.create.start');
</script>

<template>
    <Head :title="`${departures?.stop.name}: departures`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('new_post.title') }}
            </h2>
        </template>
        <DeparturesFilter
            :latitude="latitude"
            :longitude="longitude"
            :filter="filter"
            :request-time="requestTime"
            :location="requestIdentifier ? departures?.stop : null"
        />
        <div class="card bg-base-100 min-w-full shadow-md">
            <!-- Results -->
            <ul class="list">
                <li class="p-4 pb-2 text-xs tracking-wide opacity-60">
                    {{
                        t('new_post.departures_at_stop', {
                            location: departures?.stop.name || 'unknown',
                            time: time
                                ? time?.hasSame(DateTime.now(), 'day')
                                    ? time.toLocaleString(DateTime.TIME_SIMPLE)
                                    : time.toLocaleString(DateTime.DATETIME_MED)
                                : 'unknown',
                        })
                    }}
                </li>
                <DeparturesListEntry
                    v-for="(departure, index) in departures?.departures"
                    :key="index"
                    :stop-time="departure"
                    :show-start-button="showStartButton"
                    :stop="departures!.stop"
                />
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
