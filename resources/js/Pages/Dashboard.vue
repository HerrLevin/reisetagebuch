<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Timeline from '@/Pages/Dashboard/Timeline.vue';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Head } from '@inertiajs/vue3';
import type { PropType } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    posts: {
        type: Array as PropType<Array<BasePost | TransportPost | LocationPost>>,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="t('pages.timeline.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('pages.timeline.title') }}
            </h2>
        </template>

        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <Timeline :posts />
        </div>
    </AuthenticatedLayout>
</template>
