<script setup lang="ts">
import { api } from '@/app';
import Loading from '@/Components/Loading.vue';
import Map from '@/Components/Map.vue';
import Post from '@/Components/Post/Post.vue';
import PostMetaInfo from '@/Components/Post/PostMetaInfo.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getColorForPost } from '@/Services/ApiDepartureTypeService';
import {
    getArrivalDelay,
    getArrivalTime,
    getDepartureDelay,
    getDepartureTime,
} from '@/Services/TripTimeService';
import { isApiLocationPost, isApiTransportPost } from '@/types/PostTypes';
import { Head, usePage } from '@inertiajs/vue3';
import { GeometryCollection } from 'geojson';
import { ArrowLeft } from 'lucide-vue-next';
import { LngLat } from 'maplibre-gl';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { BasePost, LocationPost, TransportPost } from '../../types/Api.gen';

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
    api.posts
        .showPost(props.postId)
        .then((response) => {
            if (!response.data) {
                post.value = null;
                return;
            }
            post.value = response.data;
            heading.value = t('posts.name_post', {
                name: post.value?.user.name,
            });
            getPageTitle();
            mapPostDetails();
        })
        .catch(() => {
            post.value = null;
        })
        .finally(() => {
            loading.value = false;
        });
}

function mapPostDetails() {
    if (isApiLocationPost(post.value)) {
        startPoint.value = new LngLat(
            (post.value as LocationPost).location?.longitude ?? 9,
            (post.value as LocationPost).location?.latitude ?? 49,
        );
    } else if (isApiTransportPost(post.value)) {
        const tPost = post.value as TransportPost;
        startPoint.value = new LngLat(
            tPost.originStop.location.longitude ?? 9,
            tPost.originStop.location.latitude ?? 49,
        );
        endPoint.value = new LngLat(
            tPost.destinationStop.location.longitude ?? 9,
            tPost.destinationStop.location.latitude ?? 49,
        );

        api.map
            .getLineStringBetween({
                from: tPost.originStop.id,
                to: tPost.destinationStop.id,
            })
            .then((response) => {
                lineString.value = response.data as GeometryCollection;
            })
            .catch(() => {
                lineString.value = null;
            });

        api.map
            .getStopsBetween({
                from: tPost.originStop.id,
                to: tPost.destinationStop.id,
            })
            .then((response) => {
                stopovers.value = response.data as GeometryCollection;
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
    if (isApiLocationPost(post.value)) {
        pageTitle.value = t('posts.name_location_post', {
            name: post.value.user.name,
            location: post.value.location.name,
        });
    } else if (isApiTransportPost(post.value)) {
        const tPost = post.value as TransportPost;
        pageTitle.value = t('posts.name_transport_post', {
            name: tPost.user.name,
            from: tPost.originStop.location.name,
            to: tPost.destinationStop.location.name,
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
    if (!isApiTransportPost(post.value)) return 0;

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

function deleted() {
    window.location.href = route('dashboard');
}
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
                    isApiTransportPost(post) ? getColorForPost(post) : undefined
                "
                :progress="progress"
            ></Map>
            <div class="p-4">
                <ul class="list">
                    <li class="list-row">
                        <Loading v-if="loading" class="mx-auto my-4" />
                        <Post v-if="post" :post @delete:post="deleted()" />
                    </li>
                </ul>
            </div>
        </div>

        <PostMetaInfo v-if="post" :meta-infos="post.metaInfos" class="mt-4" />
    </AuthenticatedLayout>
</template>
