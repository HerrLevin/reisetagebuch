<script setup lang="ts">
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Timeline from '@/Pages/Dashboard/Timeline.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
useTitle(t('pages.timeline.title'));
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('pages.timeline.title') }}
            </h2>
        </template>

        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <Timeline />
        </div>
    </AuthenticatedLayout>
</template>
