<script setup lang="ts">
import Map from '@/Components/Map.vue';
import Post from '@/Components/Post/Post.vue';
import ArrowLeft from '@/Icons/ArrowLeft.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    BasePost,
    isLocationPost,
    isTransportPost,
    LocationPost,
    TransportPost,
} from '@/types/PostTypes';
import { Head } from '@inertiajs/vue3';
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

if (isLocationPost(props.post)) {
    startPoint.value = new LngLat(
        (props.post as LocationPost).location?.longitude ?? 9,
        (props.post as LocationPost).location?.latitude ?? 49,
    );
} else if (isTransportPost(props.post)) {
    startPoint.value = new LngLat(
        (props.post as TransportPost).start?.longitude ?? 9,
        (props.post as TransportPost).start?.latitude ?? 49,
    );
    endPoint.value = new LngLat(
        (props.post as TransportPost).stop?.longitude ?? 9,
        (props.post as TransportPost).stop?.latitude ?? 49,
    );
} else {
    startPoint.value = null;
    endPoint.value = null;
}
</script>

<template>
    <Head title="" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Dashboard</h2>
        </template>

        <div class="card bg-base-100 min-w-full shadow-md">
            <div class="border-base-300 flex items-center gap-2 border-b-1 p-4">
                <button
                    class="btn btn-ghost btn-sm text-base-content normal-case"
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
