<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
defineProps({
    class: {
        type: String,
        default: 'w-20 h-20 fill-current',
    },
});
</script>
<template>
    <!-- eslint-disable-next-line -->
    <img src="/assets/logo.webp" :alt="t('app.logo')" :class />
</template>
