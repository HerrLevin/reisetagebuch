<script setup lang="ts">
defineProps<{
    licenseAttribution: string | null | undefined;
    licenseUrl: string | null | undefined;
}>();
</script>

<template>
    <div
        v-if="licenseAttribution"
        class="text-center text-[0.65rem] opacity-65"
    >
        <a
            v-if="licenseUrl"
            :href="licenseUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="hover:underline"
        >
            {{ licenseAttribution }}
        </a>
        <span v-else>{{ licenseAttribution }}</span>
    </div>
</template>
