<script setup lang="ts">
import {
    MglCircleLayer,
    MglGeoJsonSource,
    MglLineLayer,
    MglMap,
    MglRasterLayer,
    MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import type { Feature, FeatureCollection } from 'geojson';
import {
    LngLat,
    LngLatBounds,
    LngLatBoundsLike,
    StyleSpecification,
} from 'maplibre-gl';
import { PropType, ref } from 'vue';

const props = defineProps({
    startPoint: {
        type: Object as PropType<LngLat>,
        default: new LngLat(8.403, 49),
        required: false,
    },
    endPoint: {
        type: Object as PropType<LngLat | null>,
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
    const mapBounds = new LngLatBounds();
    mapBounds.extend(props.startPoint);
    mapBounds.extend(props.endPoint);
    bounds.value = mapBounds;
} else {
    bounds.value = undefined;
}
const geoJsonSource = ref({
    type: 'FeatureCollection',
    features: [
        {
            type: 'Feature',
            properties: {},
            geometry: {
                type: 'LineString',
                coordinates: [
                    [props.startPoint.lng, props.startPoint.lat],
                    ...(props.endPoint
                        ? [[props.endPoint.lng, props.endPoint.lat]]
                        : []),
                ],
            },
        },
    ],
} as FeatureCollection);
const geoJsonA = ref({
    type: 'FeatureCollection',
    features: [],
} as FeatureCollection);

function getPointFeature(
    lng: number,
    lat: number,
    properties: Record<string, unknown> = {},
): Feature {
    return {
        type: 'Feature',
        properties,
        geometry: {
            type: 'Point',
            coordinates: [lng, lat],
        },
    };
}

geoJsonA.value.features.push(
    getPointFeature(props.startPoint.lng, props.startPoint.lat),
);

if (props.endPoint) {
    geoJsonA.value.features.push(
        getPointFeature(props.endPoint.lng, props.endPoint.lat),
    );
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
        <mgl-geo-json-source :data="geoJsonSource" source-id="geojson">
            <mgl-line-layer
                layer-id="line"
                :source-id="'geojson'"
                :layout="{
                    'line-cap': 'round',
                    'line-join': 'round',
                    visibility: 'visible',
                }"
                :paint="{
                    'line-color': '#FF0000',
                    'line-width': 4,
                    'line-opacity': 0.8,
                }"
            />
        </mgl-geo-json-source>
        <mgl-geo-json-source source-id="points" :data="geoJsonA">
            <mgl-circle-layer
                layer-id="points"
                :paint="{
                    'circle-radius': 6,
                    'circle-color': '#007cbf',
                    'circle-stroke-color': '#fff',
                    'circle-stroke-width': 2,
                }"
            >
            </mgl-circle-layer>
        </mgl-geo-json-source>
    </mgl-map>
</template>

<style scoped></style>
