<script setup lang="ts">
import { api } from '@/api';
import BaseMapWrapper from '@/Components/Maps/BaseMapWrapper.vue';
import {
    MglFillLayer,
    MglGeoJsonSource,
    MglPopup,
    useMap,
} from '@indoorequal/vue-maplibre-gl';
import type { Feature, FeatureCollection, Geometry } from 'geojson';
import {
    LngLatBounds,
    type LngLatBoundsLike,
    type LngLatLike,
    type MapLayerMouseEvent,
} from 'maplibre-gl';
import { computed, ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const VISITED_COLOR = '#007cbf';
const TRANSITED_COLOR = '#a8d5e8';
const LAYER_ID = 'countries-fill';

const countriesGeoJson = ref<FeatureCollection>();
const visited = ref<string[]>([]);
const transitedOnly = ref<string[]>([]);
const bounds = ref<LngLatBoundsLike | undefined>(undefined);

function extendBoundsWithGeometry(
    mapBounds: LngLatBounds,
    geometry: Geometry,
): void {
    if (geometry.type === 'Polygon') {
        geometry.coordinates.forEach((ring) =>
            ring.forEach((point) =>
                mapBounds.extend(point as [number, number]),
            ),
        );
    } else if (geometry.type === 'MultiPolygon') {
        geometry.coordinates.forEach((polygon) =>
            polygon.forEach((ring) =>
                ring.forEach((point) =>
                    mapBounds.extend(point as [number, number]),
                ),
            ),
        );
    }
}

function boundsForCountryCodes(
    geojson: FeatureCollection,
    codes: string[],
): LngLatBoundsLike | undefined {
    if (codes.length === 0) {
        return undefined;
    }

    const codeSet = new Set(codes);
    const mapBounds = new LngLatBounds();
    geojson.features.forEach((feature: Feature) => {
        if (codeSet.has(feature.properties?.iso_a2)) {
            extendBoundsWithGeometry(mapBounds, feature.geometry);
        }
    });

    return mapBounds.isEmpty() ? undefined : mapBounds;
}

Promise.all([
    fetch('/countries.geojson').then((response) => response.json()),
    api.statistics.getCountriesForUser(),
])
    .then(([geojson, countriesResponse]) => {
        countriesGeoJson.value = geojson;
        visited.value = countriesResponse.data.visited;
        transitedOnly.value = countriesResponse.data.transitedOnly;
        bounds.value = boundsForCountryCodes(geojson, visited.value);
    })
    .catch((error) => {
        console.error('Error fetching countries data:', error);
    });

const map = useMap();
const hoveredCountryName = ref<string | null>(null);
const hoveredCoordinates = ref<LngLatLike | null>(null);
let hoverListenersRegistered = false;
let hoveredFeatureId: string | number | undefined;

function isVisitedOrTransited(isoA2: string | undefined): boolean {
    return (
        !!isoA2 &&
        (visited.value.includes(isoA2) || transitedOnly.value.includes(isoA2))
    );
}

function setHoverState(id: string | number | undefined, hover: boolean) {
    if (id === undefined) {
        return;
    }
    map.map?.setFeatureState({ source: 'countries', id }, { hover });
}

watchEffect(() => {
    if (hoverListenersRegistered || !map.isLoaded || !countriesGeoJson.value) {
        return;
    }
    hoverListenersRegistered = true;

    map.map?.on('mousemove', LAYER_ID, (e: MapLayerMouseEvent) => {
        const feature = e.features?.[0];
        const isoA2 = feature?.properties?.iso_a2 as string | undefined;
        const visitedOrTransited = isVisitedOrTransited(isoA2);

        if (map.map) {
            map.map.getCanvas().style.cursor = visitedOrTransited
                ? 'pointer'
                : '';
        }

        if (
            hoveredFeatureId !== undefined &&
            hoveredFeatureId !== feature?.id
        ) {
            setHoverState(hoveredFeatureId, false);
            hoveredFeatureId = undefined;
        }

        if (feature?.id !== undefined) {
            hoveredFeatureId = feature.id;
            setHoverState(hoveredFeatureId, true);
        }

        if (visitedOrTransited) {
            hoveredCountryName.value =
                (feature?.properties?.name as string) ?? null;
            hoveredCoordinates.value = e.lngLat;
        } else {
            hoveredCountryName.value = null;
            hoveredCoordinates.value = null;
        }
    });

    map.map?.on('mouseleave', LAYER_ID, () => {
        if (map.map) {
            map.map.getCanvas().style.cursor = '';
        }
        setHoverState(hoveredFeatureId, false);
        hoveredFeatureId = undefined;
        hoveredCountryName.value = null;
        hoveredCoordinates.value = null;
    });
});

const fillColor = computed(
    () =>
        [
            'match',
            ['get', 'iso_a2'],
            visited.value.length > 0 ? visited.value : [''],
            VISITED_COLOR,
            transitedOnly.value.length > 0 ? transitedOnly.value : [''],
            TRANSITED_COLOR,
            'rgba(0, 0, 0, 0)',
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
        ] as any,
);

const fillOpacity = computed(
    () =>
        [
            'case',
            ['boolean', ['feature-state', 'hover'], false],
            0.85,
            0.6,
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
        ] as any,
);
</script>

<template>
    <BaseMapWrapper
        :globe-projection="false"
        :zoom="2"
        :bounds="bounds"
        :fit-bounds-options="{ padding: 40, maxZoom: 6 }"
        height="40vh"
    >
        <mgl-geo-json-source
            v-if="countriesGeoJson"
            source-id="countries"
            :data="countriesGeoJson"
            promote-id="iso_a2"
        >
            <mgl-fill-layer
                :layer-id="LAYER_ID"
                :paint="{
                    'fill-color': fillColor,
                    'fill-opacity': fillOpacity,
                }"
            >
            </mgl-fill-layer>
        </mgl-geo-json-source>
        <mgl-popup
            v-if="hoveredCountryName && hoveredCoordinates"
            :coordinates="hoveredCoordinates"
            :text="hoveredCountryName"
            :close-button="false"
            :close-on-click="false"
        />
    </BaseMapWrapper>
    <div class="flex flex-wrap gap-4 p-4 text-sm">
        <span class="flex items-center gap-2">
            <span
                class="inline-block h-3 w-3 rounded"
                :style="{ backgroundColor: VISITED_COLOR }"
            ></span>
            {{ t('statistics.visited') }}
        </span>
        <span class="flex items-center gap-2">
            <span
                class="inline-block h-3 w-3 rounded"
                :style="{ backgroundColor: TRANSITED_COLOR }"
            ></span>
            {{ t('statistics.transited') }}
        </span>
    </div>
</template>
