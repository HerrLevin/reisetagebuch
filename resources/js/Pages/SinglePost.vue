<script setup lang="ts">
import Map from '@/Components/Map.vue';
import Post from '@/Components/Post/Post.vue';
import PostMetaInfo from '@/Components/Post/PostMetaInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getColorForPost } from '@/Services/DepartureTypeService';
import {
    getArrivalDelay,
    getArrivalTime,
    getDepartureDelay,
    getDepartureTime,
} from '@/Services/TripTimeService';
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
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Loading from '@/Components/Loading.vue';

const { t } = useI18n();

const props = defineProps({
    postId: {
        type: String,
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
const heading = ref('');
const pageTitle = ref('');
const loading = ref(false);

const post = ref<BasePost | TransportPost | LocationPost | null>(null);

function fetchPost() {
    loading.value = true;
    axios
        .get('/api/posts/' + props.postId)
        .then((response) => {
            post.value = response.data;
            heading.value = t('posts.name_post', {
                name: post.value?.user.name,
            });
            getPageTitle();
            mapPostDetails();
            loading.value = false;
        })
        .catch(() => {
            post.value = null;
        });
}

function mapPostDetails() {
    if (isLocationPost(post.value)) {
        startPoint.value = new LngLat(
            (post.value as LocationPost).location?.longitude ?? 9,
            (post.value as LocationPost).location?.latitude ?? 49,
        );
    } else if (isTransportPost(post.value)) {
        startPoint.value = new LngLat(
            (post.value as TransportPost).originStop.location.longitude ?? 9,
            (post.value as TransportPost).originStop.location.latitude ?? 49,
        );
        endPoint.value = new LngLat(
            (post.value as TransportPost).destinationStop.location.longitude ??
                9,
            (post.value as TransportPost).destinationStop.location.latitude ??
                49,
        );

        axios
            .get(
                '/api/map/linestring/' +
                    post.value.originStop.id +
                    '/' +
                    post.value.destinationStop.id,
            )
            .then((response) => {
                lineString.value = response.data;
            })
            .catch(() => {
                lineString.value = null;
            });

        axios
            .get(
                '/api/map/stopovers/' +
                    post.value.originStop.id +
                    '/' +
                    post.value.destinationStop.id,
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
}

function getPageTitle() {
    pageTitle.value = heading.value;
    if (isLocationPost(post.value)) {
        pageTitle.value = t('posts.name_location_post', {
            name: post.value.user.name,
            location: post.value.location.name,
        });
    } else if (isTransportPost(post.value)) {
        pageTitle.value = t('posts.name_transport_post', {
            name: post.value.user.name,
            from: post.value.originStop.location.name,
            to: post.value.destinationStop.location.name,
        });
    }
    if (post.value?.body) {
        pageTitle.value += `: ${post.value.body}`;
    }
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

watch(() => props.postId, fetchPost, { immediate: true });

const progress = computed(() => {
    if (!isTransportPost(post.value)) return 0;

    const transportPost = post.value as TransportPost;
    const departureDelay = getDepartureDelay(transportPost) || 0;
    const departureTime = getDepartureTime(transportPost.originStop)
        ?.plus({ minutes: departureDelay })
        .toISO();
    const arrivalDelay = getArrivalDelay(transportPost) || 0;
    const arrivalTime = getArrivalTime(transportPost.destinationStop)
        ?.plus({ minutes: arrivalDelay })
        .toISO();

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
    <Head :title="pageTitle" />

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
                v-if="post && startPoint"
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
                        <Loading v-if="loading" class="my-4 mx-auto" />
                        <Post v-if="post" :post />
                    </li>
                </ul>
            </div>
        </div>

        <PostMetaInfo v-if="post" :meta-infos="post.metaInfos" class="mt-4" />
    </AuthenticatedLayout>
</template>
