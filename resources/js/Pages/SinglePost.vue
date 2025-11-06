<script setup lang="ts">
import Map from '@/Components/Map.vue';
import Post from '@/Components/Post/Post.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getColorForPost } from '@/Services/DepartureTypeService';
import { getArrivalTime, getDepartureTime } from '@/Services/TripTimeService';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { GeometryCollection } from 'geojson';
import { ArrowLeft } from 'lucide-vue-next';
import { LngLat } from 'maplibre-gl';
import { computed, onMounted, onUnmounted, PropType, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    post: {
        type: Object as PropType<BasePost | TransportPost | LocationPost>,
        required: true,
    },
});

const goBack = () => {
    window.history.back();
};
const startPoint = ref(null as LngLat | null);
const endPoint = ref(null as LngLat | null);
const lineString = ref(null as GeometryCollection | null);
const stopovers = ref(null as GeometryCollection | null);

if (isLocationPost(props.post)) {
    startPoint.value = new LngLat(
        (props.post as LocationPost).location?.longitude ?? 9,
        (props.post as LocationPost).location?.latitude ?? 49,
    );
} else if (isTransportPost(props.post)) {
    startPoint.value = new LngLat(
        (props.post as TransportPost).originStop.location.longitude ?? 9,
        (props.post as TransportPost).originStop.location.latitude ?? 49,
    );
    endPoint.value = new LngLat(
        (props.post as TransportPost).destinationStop.location.longitude ?? 9,
        (props.post as TransportPost).destinationStop.location.latitude ?? 49,
    );

    axios
        .get(
            '/map/linestring/' +
                props.post.originStop.id +
                '/' +
                props.post.destinationStop.id,
        )
        .then((response) => {
            lineString.value = response.data;
        })
        .catch(() => {
            lineString.value = null;
        });

    axios
        .get(
            '/map/stopovers/' +
                props.post.originStop.id +
                '/' +
                props.post.destinationStop.id,
        )
        .then((response) => {
            stopovers.value = response.data;
        })
        .catch(() => {
            stopovers.value = null;
        });
} else {
    startPoint.value = null;
    endPoint.value = null;
}
const heading = t('posts.name_post', { name: props.post.user.name });
let head = heading;
if (isLocationPost(props.post)) {
    head = t('posts.name_location_post', {
        name: props.post.user.name,
        location: props.post.location.name,
    });
} else if (isTransportPost(props.post)) {
    head = t('posts.name_transport_post', {
        name: props.post.user.name,
        from: props.post.originStop.location.name,
        to: props.post.destinationStop.location.name,
    });
}
if (props.post.body) {
    head += `: ${props.post.body}`;
}

const currentTime = ref(Date.now());
let intervalId: number | null = null;

onMounted(() => {
    intervalId = window.setInterval(() => {
        currentTime.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

const progress = computed(() => {
    if (!isTransportPost(props.post)) return 0;

    const transportPost = props.post as TransportPost;
    const departureTime =
        transportPost.manualDepartureTime ||
        getDepartureTime(transportPost.originStop)?.toISO();
    const arrivalTime =
        transportPost.manualArrivalTime ||
        getArrivalTime(transportPost.destinationStop)?.toISO();

    if (!departureTime || !arrivalTime) return 0;

    const departure = new Date(departureTime).getTime();
    const arrival = new Date(arrivalTime).getTime();
    const now = currentTime.value;

    if (now < departure) return 0;
    if (now > arrival) return 100;

    const totalDuration = arrival - departure;
    const elapsed = now - departure;

    return Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
});
</script>

<template>
    <Head :title="head" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ heading }}
            </h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <div class="border-base-300 flex items-center gap-2 border-b-1 p-4">
                <button
                    class="btn btn-ghost btn-sm btn-circle text-base-content normal-case"
                    type="button"
                    @click="goBack"
                >
                    <ArrowLeft class="size-6" />
                </button>
            </div>
            <Map
                v-if="startPoint"
                :start-point="startPoint"
                :end-point="endPoint"
                :line-string="lineString"
                :stop-overs="stopovers"
                :show-geo-position="
                    usePage().props.auth.user?.id === post.user.id
                "
                :line-color="
                    isTransportPost(post) ? getColorForPost(post) : undefined
                "
                :progress="progress"
            ></Map>
            <div class="p-4">
                <ul class="list">
                    <li class="list-row">
                        <Post :post />
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
