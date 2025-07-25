<script setup lang="ts">
import { UserDto } from '@/types';
import {
    MglCircleLayer,
    MglGeoJsonSource,
    MglHeatmapLayer,
    MglMap,
    MglRasterLayer,
    MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import type { GeometryCollection } from 'geojson';
import {
    LngLat,
    LngLatBounds,
    LngLatBoundsLike,
    StyleSpecification,
} from 'maplibre-gl';
import { PropType, ref } from 'vue';

const props = defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
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

const zoom = 8;
const center = new LngLat(8.403, 49);
const bounds = ref(undefined as LngLatBoundsLike | undefined);
const geoJson = ref(undefined as GeometryCollection | undefined);

fetch('/profile/' + props.user.username + '/map-data')
    .then((response) => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then((json) => {
        geoJson.value = json;
        const mapBounds = new LngLatBounds();
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        json.geometries.forEach((geometry: any) => {
            mapBounds.extend(geometry.coordinates);
        });
        bounds.value = mapBounds;
    })
    .catch((error) => {
        console.error('Error fetching data:', error);
    });
</script>

<template>
    <mgl-map
        :map-style="style"
        :zoom="zoom"
        :max-zoom="18"
        height="100vh"
        :center="center"
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
        <mgl-geo-json-source
            v-if="geoJson"
            source-id="locations"
            :data="geoJson"
        >
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
            <mgl-heatmap-layer
                layer-id="heatmap"
                :paint="{
                    'heatmap-radius': [
                        'interpolate',
                        ['linear'],
                        ['zoom'],
                        0,
                        2,
                        9,
                        20,
                    ],
                    'heatmap-weight': {
                        property: 'weight',
                        type: 'exponential',
                        stops: [
                            [0, 0],
                            [1, 1],
                        ],
                    },
                    'heatmap-intensity': [
                        'interpolate',
                        ['linear'],
                        ['zoom'],
                        0,
                        1,
                        9,
                        3,
                    ],
                    // Transition from heatmap to circle layer by zoom level
                    'heatmap-opacity': [
                        'interpolate',
                        ['linear'],
                        ['zoom'],
                        7,
                        1,
                        9,
                        0,
                    ],
                    'heatmap-color': [
                        'interpolate',
                        ['linear'],
                        ['heatmap-density'],
                        0,
                        'rgba(33,102,172,0)',
                        0.2,
                        'rgb(103,169,207)',
                        0.4,
                        'rgb(209,229,240)',
                        0.6,
                        'rgb(253,219,199)',
                        0.8,
                        'rgb(239,138,98)',
                        1,
                        'rgb(178,24,43)',
                    ],
                }"
            >
            </mgl-heatmap-layer>
        </mgl-geo-json-source>
    </mgl-map>
</template>

<style scoped></style>
