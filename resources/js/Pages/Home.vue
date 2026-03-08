<script setup lang="ts">
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GlobalTimeline from '@/Pages/Home/GlobalTimeline.vue';
import Timeline from '@/Pages/Home/Timeline.vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

const { t } = useI18n();
useTitle(t('pages.timeline.title'));
const route = useRoute();
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('pages.timeline.title') }}
            </h2>
        </template>

        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <Timeline v-if="route.name === 'home'" />
            <GlobalTimeline v-else-if="route.name === 'home.global'" />
        </div>
    </AuthenticatedLayout>
</template>
