<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const version = computed(() => page.props.appVersion || '0.0.0');
</script>

<template>
    <footer class="py-4 text-center text-xs opacity-50">
        <div>{{ version }}</div>
    </footer>
</template>
