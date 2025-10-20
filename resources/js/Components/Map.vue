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
import type { Feature, FeatureCollection, GeometryCollection } from 'geojson';
import {
    LngLat,
    LngLatBounds,
    LngLatBoundsLike,
    StyleSpecification,
} from 'maplibre-gl';
import { PropType, ref, watch } from 'vue';

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
    showGeoPosition: {
        type: Boolean,
        default: false,
        required: false,
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
            geoJsonSource.value = {
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
            } as FeatureCollection;
        }

        const mapBounds = new LngLatBounds();
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
            :show-zoom="true"
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
