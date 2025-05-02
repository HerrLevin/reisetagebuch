<script setup lang="ts">
import {
    MglMap,
    MglMarker,
    MglRasterLayer,
    MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import {
    LngLat,
    LngLatBounds,
    LngLatBoundsLike,
    LngLatLike,
    StyleSpecification,
} from 'maplibre-gl';
import { PropType, ref } from 'vue';

const props = defineProps({
    startPoint: {
        type: Object as PropType<LngLatLike>,
        default: new LngLat(8.403, 49),
        required: false,
    },
    endPoint: {
        type: Object as PropType<LngLatLike | null>,
        default: null,
        required: false,
    },
});

const style = {
    version: 8,
    sources: {},
    layers: [],
} as StyleSpecification;

const osmTiles = ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'];
const osmAttribution =
    'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';

const zoom = 14;
const bounds = ref(undefined as LngLatBoundsLike | undefined);
if (props.startPoint && props.endPoint) {
    bounds.value = new LngLatBounds([props.endPoint, props.startPoint]);
} else {
    bounds.value = undefined;
}
</script>

<template>
    <mgl-map
        :map-style="style"
        :center="startPoint"
        :zoom="zoom"
        :max-zoom="18"
        height="25vh"
        :scroll-zoom="false"
        :bounds="bounds"
        :fit-bounds-options="{
            padding: 20,
            maxZoom: 16,
        }"
    >
        <mgl-raster-source
            source-id="raster-source"
            :tiles="osmTiles"
            :tile-size="256"
            :maxzoom="18"
            :attribution="osmAttribution"
        >
            <mgl-raster-layer layer-id="raster-layer" />
        </mgl-raster-source>

        <mgl-marker :coordinates="startPoint"></mgl-marker>
        <mgl-marker v-if="endPoint" :coordinates="endPoint"></mgl-marker>
    </mgl-map>
</template>

<style scoped></style>
