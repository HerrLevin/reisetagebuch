<script setup lang="ts">
import {
    MglCircleLayer,
    MglFullscreenControl,
    MglGeoJsonSource,
    MglGeolocateControl,
    MglLineLayer,
    MglMap,
    MglNavigationControl,
    MglRasterLayer,
    MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import type {
    Feature,
    FeatureCollection,
    GeometryCollection,
    MultiPoint,
} from 'geojson';
import {
    LngLat,
    LngLatBounds,
    LngLatBoundsLike,
    StyleSpecification,
} from 'maplibre-gl';
import { computed, PropType, ref, watch } from 'vue';

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
    lineString: {
        type: Object as PropType<GeometryCollection | null>,
        default: null,
        required: false,
    },
    stopOvers: {
        type: Object as PropType<MultiPoint | null>,
        default: null,
        required: false,
    },
    showGeoPosition: {
        type: Boolean,
        default: false,
        required: false,
    },
    lineColor: {
        type: String,
        default: '#007cbf',
        required: false,
    },
    progress: {
        type: Number,
        default: 0,
        required: false,
        validator: (value: number) => value >= 0 && value <= 100,
    },
});

const style = {
    version: 8,
    projection: { type: 'globe' },
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
const geoJsonSource = ref(null as FeatureCollection | null);
const stopOverSource = ref(null as FeatureCollection | null);
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

function interpolatePointOnLine(
    coordinates: number[][],
    percentage: number,
): [number, number] | null {
    if (!coordinates || coordinates.length < 2) return null;

    let totalDistance = 0;
    const distances: number[] = [0];

    for (let i = 1; i < coordinates.length; i++) {
        const [lng1, lat1] = coordinates[i - 1];
        const [lng2, lat2] = coordinates[i];
        const distance = Math.sqrt(
            Math.pow(lng2 - lng1, 2) + Math.pow(lat2 - lat1, 2),
        );
        totalDistance += distance;
        distances.push(totalDistance);
    }

    const targetDistance = (percentage / 100) * totalDistance;

    for (let i = 1; i < coordinates.length; i++) {
        if (distances[i] >= targetDistance) {
            const segmentDistance = distances[i] - distances[i - 1];
            const segmentProgress =
                (targetDistance - distances[i - 1]) / segmentDistance;

            const [lng1, lat1] = coordinates[i - 1];
            const [lng2, lat2] = coordinates[i];

            const lng = lng1 + (lng2 - lng1) * segmentProgress;
            const lat = lat1 + (lat2 - lat1) * segmentProgress;

            return [lng, lat];
        }
    }

    return coordinates[coordinates.length - 1] as [number, number];
}

function fallbackLinestring() {
    if (props.startPoint && props.endPoint) {
        const coordinates = [];

        coordinates.push([props.startPoint.lng, props.startPoint.lat]);
        if (props.endPoint) {
            coordinates.push([props.endPoint.lng, props.endPoint.lat]);
        }

        if (props.stopOvers) {
            for (const coord of props.stopOvers.coordinates) {
                coordinates.splice(
                    coordinates.length - 1,
                    0,
                    coord as number[],
                );
            }
        }

        geoJsonSource.value = {
            type: 'FeatureCollection',
            features: [
                {
                    type: 'Feature',
                    properties: {},
                    geometry: {
                        type: 'LineString',
                        coordinates: coordinates,
                    },
                },
            ],
        } as FeatureCollection;
    } else {
        geoJsonSource.value = null;
    }
}

watch(
    () => props.stopOvers,
    (newStopOvers) => {
        if (newStopOvers) {
            // remove first and last point (start and end)
            newStopOvers.coordinates = newStopOvers.coordinates.slice(
                1,
                newStopOvers.coordinates.length - 1,
            );
            stopOverSource.value = {
                type: 'FeatureCollection',
                features: [
                    {
                        type: 'Feature',
                        properties: {},
                        geometry: newStopOvers,
                    },
                ],
            };
            if (!props.lineString) {
                fallbackLinestring();
            }
        } else {
            stopOverSource.value = null;
        }
    },
    { immediate: true },
);

// watch props.lineString to update geoJsonSource
watch(
    () => props.lineString,
    (newLineString) => {
        if (newLineString) {
            geoJsonSource.value = {
                type: 'FeatureCollection',
                features: [
                    {
                        type: 'Feature',
                        properties: {},
                        geometry: newLineString,
                    },
                ],
            };
        } else {
            fallbackLinestring();
        }

        const mapBounds = new LngLatBounds();
        if (!geoJsonSource.value) {
            bounds.value = undefined;
            return;
        }

        for (const feature of geoJsonSource.value.features || []) {
            if (feature.geometry.type === 'Point') {
                mapBounds.extend(
                    new LngLat(
                        feature.geometry.coordinates[0],
                        feature.geometry.coordinates[1],
                    ),
                );
            } else if (feature.geometry.type === 'LineString') {
                for (const coordinate of feature.geometry.coordinates || []) {
                    mapBounds.extend(new LngLat(coordinate[0], coordinate[1]));
                }
            }
        }
        bounds.value = mapBounds;
    },
    { immediate: true },
);

const animatedPointSource = computed(() => {
    if (
        !geoJsonSource.value ||
        !geoJsonSource.value.features.length ||
        props.progress >= 100
    ) {
        return null;
    }

    const feature = geoJsonSource.value.features[0];
    let coordinates: number[][] = [];

    if (feature.geometry.type === 'LineString') {
        coordinates = feature.geometry.coordinates as number[][];
    } else if (feature.geometry.type === 'GeometryCollection') {
        const lineString = feature.geometry.geometries.find(
            (g) => g.type === 'LineString',
        );
        if (lineString && lineString.type === 'LineString') {
            coordinates = lineString.coordinates as number[][];
        }
    }

    const point = interpolatePointOnLine(coordinates, props.progress);
    if (!point) return null;

    return {
        type: 'FeatureCollection',
        features: [
            {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'Point',
                    coordinates: point,
                },
            },
        ],
    } as FeatureCollection;
});
</script>

<template>
    <mgl-map
        :map-style="style"
        :center="startPoint"
        :zoom="zoom"
        :max-zoom="18"
        height="50vh"
        :bounds="bounds"
        :fit-bounds-options="{
            padding: 40,
            maxZoom: 16,
        }"
    >
        <mgl-fullscreen-control />
        <mgl-navigation-control
            position="top-right"
            :show-zoom="false"
            :show-compass="true"
        />
        <mgl-geolocate-control
            v-if="showGeoPosition"
            position="top-right"
            :fit-bounds-options="{
                maxZoom: 16,
            }"
            :track-user-location="true"
            :show-user-location="true"
        />
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
            v-if="geoJsonSource"
            :data="geoJsonSource"
            source-id="geoJson"
        >
            <mgl-line-layer
                layer-id="line"
                :source-id="'geoJson'"
                :layout="{
                    'line-cap': 'round',
                    'line-join': 'round',
                    visibility: 'visible',
                }"
                :paint="{
                    'line-color': lineColor,
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
                    'circle-color': lineColor,
                    'circle-stroke-color': '#fff',
                    'circle-stroke-width': 2,
                }"
            >
            </mgl-circle-layer>
        </mgl-geo-json-source>
        <mgl-geo-json-source
            v-if="stopOverSource"
            source-id="stops"
            :data="stopOverSource"
        >
            <mgl-circle-layer
                layer-id="stopsResource"
                :paint="{
                    'circle-radius': 2,
                    'circle-color': '#fff',
                    'circle-stroke-color': lineColor,
                    'circle-stroke-width': 2,
                }"
            >
            </mgl-circle-layer>
        </mgl-geo-json-source>
        <mgl-geo-json-source
            v-if="animatedPointSource"
            source-id="animated-point"
            :data="animatedPointSource"
        >
            <mgl-circle-layer
                layer-id="animated-point"
                :paint="{
                    'circle-radius': 8,
                    'circle-color': '#007cbf',
                    'circle-stroke-color': '#fff',
                    'circle-stroke-width': 3,
                    'circle-opacity': 0.9,
                }"
            >
            </mgl-circle-layer>
        </mgl-geo-json-source>
    </mgl-map>
</template>
