<script setup lang="ts">
import ErrorLayout from '@/Pages/Errors/ErrorLayout.vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>

<template>
    <ErrorLayout
        code="403"
        :title="t('errors.403.title')"
        :message="t('errors.403.message')"
        :link-text="t('errors.403.link_text')"
        :to="{ name: 'home' }"
    >
    </ErrorLayout>
</template>
