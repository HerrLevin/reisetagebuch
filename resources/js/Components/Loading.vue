<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
<template>
    <span class="loading loading-dots" role="status">
        <span class="sr-only">{{ t('verbs.loading') }}</span>
    </span>
</template>
