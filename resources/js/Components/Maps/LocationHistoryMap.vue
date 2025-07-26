<script setup lang="ts">
import { LocationHistoryDto, UserDto } from '@/types';
import {
    MglCircleLayer,
    MglGeoJsonSource,
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
    locations: {
        type: Array as PropType<LocationHistoryDto[]>,
        required: true,
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
const postsJson = ref(undefined as GeometryCollection | undefined);

const mapBounds = new LngLatBounds();
props.locations?.forEach((location) => {
    if (!geoJson.value) {
        geoJson.value = {
            type: 'GeometryCollection',
            geometries: [],
        };
    }
    if (!postsJson.value && location.name && location.name.length > 0) {
        postsJson.value = {
            type: 'GeometryCollection',
            geometries: [],
        };
    }
    const point = {
        type: 'Point',
        coordinates: [location.longitude, location.latitude],
        properties: {
            timestamp: location.timestamp,
            name: location.name,
        },
    };

    if (location.name && location.name.length > 0) {
        postsJson.value?.geometries.push(point);
    } else {
        geoJson.value.geometries.push(point);
    }

    mapBounds.extend([location.longitude, location.latitude]);
});

bounds.value = mapBounds;
</script>

<template>
    <mgl-map
        :map-style="style"
        :zoom="zoom"
        :max-zoom="18"
        height="80vh"
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
            v-if="postsJson"
            source-id="posts"
            :data="postsJson"
        >
            <mgl-circle-layer
                layer-id="posts"
                :paint="{
                    'circle-radius': 6,
                    'circle-color': '#14d1fc',
                }"
            />
        </mgl-geo-json-source>
        <mgl-geo-json-source
            v-if="geoJson"
            source-id="locations"
            :data="geoJson"
        >
            <mgl-circle-layer
                layer-id="points"
                :paint="{
                    'circle-radius': 4,
                    'circle-color': '#fc14d1',
                }"
            />
        </mgl-geo-json-source>
    </mgl-map>
</template>

<style scoped></style>
