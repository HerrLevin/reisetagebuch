<script setup lang="ts">
import BaseMapWrapper from '@/Components/Maps/BaseMapWrapper.vue';
import { LocationHistoryDto, TripHistoryEntryDto, UserDto } from '@/types';
import {
    MglCircleLayer,
    MglFullscreenControl,
    MglGeoJsonSource,
    MglLineLayer,
    MglNavigationControl,
} from '@indoorequal/vue-maplibre-gl';
import type { FeatureCollection, Geometry, GeometryCollection } from 'geojson';
import { LngLat, LngLatBounds, LngLatBoundsLike } from 'maplibre-gl';
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
    trips: {
        type: Array as PropType<TripHistoryEntryDto[]>,
        default: () => [],
    },
});

const zoom = 8;
const center = new LngLat(8.403, 49);
const bounds = ref(undefined as LngLatBoundsLike | undefined);
const geoJson = ref(undefined as GeometryCollection | undefined);
const postsJson = ref(undefined as GeometryCollection | undefined);
const tripsJson = ref(undefined as FeatureCollection | undefined);

const mapBounds = new LngLatBounds();
for (const location of props.locations) {
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
    } as Geometry;

    if (location.name && location.name.length > 0) {
        postsJson.value?.geometries.push(point);
    } else {
        geoJson.value.geometries.push(point);
    }

    mapBounds.extend([location.longitude, location.latitude]);
}

for (const trip of props.trips) {
    if (!tripsJson.value) {
        tripsJson.value = {
            type: 'FeatureCollection',
            features: [],
        };
    }
    if (trip.geometry) {
        tripsJson.value.features.push({
            type: 'Feature',
            properties: {},
            geometry: trip.geometry,
        });
        pushTripToBounds(trip.geometry);
    }
}

function pushTripToBounds(geometry: Geometry) {
    if (geometry.type === 'LineString') {
        for (const coordinate of geometry.coordinates as [number, number][]) {
            mapBounds.extend(coordinate);
        }
    } else if (geometry.type === 'Point') {
        mapBounds.extend(geometry.coordinates as [number, number]);
    } else if (
        geometry.type === 'MultiPoint' ||
        geometry.type === 'MultiLineString'
    ) {
        for (const coords of geometry.coordinates as [number, number][][]) {
            for (const coordinate of coords) {
                mapBounds.extend(coordinate);
            }
        }
    } else if (geometry.type === 'GeometryCollection') {
        for (const geom of geometry.geometries) {
            pushTripToBounds(geom);
        }
    }
}

bounds.value = mapBounds;
</script>

<template>
    <BaseMapWrapper
        :zoom="zoom"
        height="50vh"
        :center="center"
        :bounds="bounds"
    >
        <mgl-fullscreen-control />
        <mgl-navigation-control
            position="top-right"
            :show-zoom="false"
            :show-compass="true"
        />
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
                    'circle-color': '#29ff45',
                }"
            />
        </mgl-geo-json-source>

        <mgl-geo-json-source
            v-if="tripsJson"
            :data="tripsJson"
            source-id="tripsJson"
        >
            <mgl-line-layer
                layer-id="line"
                :source-id="'tripsJson'"
                :layout="{
                    'line-cap': 'round',
                    'line-join': 'round',
                    visibility: 'visible',
                }"
                :paint="{
                    'line-color': '#f700ff',
                    'line-width': 4,
                    'line-opacity': 0.3,
                }"
            />
        </mgl-geo-json-source>
    </BaseMapWrapper>
</template>
