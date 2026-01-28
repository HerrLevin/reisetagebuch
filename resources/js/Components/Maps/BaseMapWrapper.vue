<script setup lang="ts">
import { MglMap, useMap } from '@indoorequal/vue-maplibre-gl';
import { FitBoundsOptions, LngLatBoundsLike, LngLatLike } from 'maplibre-gl';
import { PropType, ref, watch } from 'vue';

const props = defineProps({
    center: {
        type: Object as PropType<LngLatLike | undefined>,
        required: false,
        default: undefined,
    },
    bounds: {
        type: Object as PropType<LngLatBoundsLike | undefined>,
        required: false,
        default: undefined,
    },
    zoom: {
        type: Number,
        default: 8,
    },
    maxZoom: {
        type: Number,
        default: 22,
    },
    fitBoundsOptions: {
        type: Object as PropType<FitBoundsOptions>,
        default: () => ({
            padding: 40,
            maxZoom: 16,
        }),
    },
    cooperativeGestures: {
        type: Boolean,
        default: true,
    },
    globeProjection: {
        type: Boolean,
        default: true,
    },
});

const storedTheme = localStorage.getItem('theme');
const lightStyleUrl = 'https://tiles.openfreemap.org/styles/positron';
const darkStyleUrl = 'https://tiles.openfreemap.org/styles/fiord';
const style = ref(lightStyleUrl);

if (storedTheme) {
    style.value = storedTheme === 'light' ? lightStyleUrl : darkStyleUrl;
} else {
    // get theme from system preference
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)')
        .matches
        ? 'dark'
        : 'light';
    style.value = systemTheme === 'light' ? lightStyleUrl : darkStyleUrl;
}

watch(
    () => localStorage.getItem('theme'),
    (newTheme) => {
        if (newTheme) {
            style.value = newTheme === 'light' ? lightStyleUrl : darkStyleUrl;
        }
    },
);

const map = useMap();
watch(
    () => map.isLoaded,
    () => {
        if (map.isLoaded && props.globeProjection) {
            map.map?.setProjection({
                type: 'globe',
            });
        }
    },
);
</script>

<template>
    <mgl-map
        :map-style="style"
        :center="center"
        :zoom="zoom"
        :max-zoom="maxZoom"
        :bounds="bounds"
        :cooperative-gestures="cooperativeGestures"
        :fit-bounds-options="fitBoundsOptions"
    >
        <slot></slot>
    </mgl-map>
</template>
