<script setup lang="ts">
import Map from '@/Components/Map.vue';
import Post from '@/Components/Post/Post.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { GeometryCollection } from 'geojson';
import { ArrowLeft } from 'lucide-vue-next';
import { LngLat } from 'maplibre-gl';
import { PropType, ref } from 'vue';

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
} else {
    startPoint.value = null;
    endPoint.value = null;
}
let head = `${props.post.user.name}'s post`;
if (isLocationPost(props.post)) {
    head = `${props.post.user.name}'s post at ${props.post.location.name}`;
} else if (isTransportPost(props.post)) {
    head = `${props.post.user.name}'s travel from ${props.post.originStop.location.name} to ${props.post.destinationStop.location.name}`;
}
if (props.post.body) {
    head += `: ${props.post.body}`;
}
</script>

<template>
    <Head :title="head" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Dashboard</h2>
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
                <p>Post</p>
            </div>
            <Map
                v-if="startPoint"
                :start-point="startPoint"
                :end-point="endPoint"
                :line-string="lineString"
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
