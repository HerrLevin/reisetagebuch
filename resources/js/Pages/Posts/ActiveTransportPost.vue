<script setup lang="ts">
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getBaseText, prettyDates } from '@/Services/PostTextService';
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useActiveTransportPostStore } from '@/stores/activeTransportPost';
import StopOverList from '@/Pages/Posts/Partials/StopOverList.vue';

const { t } = useI18n();

const title = t('active_transport_post.title');
const activePost = useActiveTransportPostStore();

const subtitle = computed(() =>
    activePost.activeTransportPost
        ? `${getBaseText(activePost.activeTransportPost)} (${prettyDates(activePost.activeTransportPost)})`
        : '',
);

function fetchActivePost() {
    activePost.fetchPost().then(() => {
        useTitle(
            activePost.activeTransportPost
                ? `${title} · ${subtitle.value}`
                : title,
        );
    });
}

onMounted(fetchActivePost);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">{{ title }}</h2>
        </template>

        <div v-if="activePost.loadingPost" class="skeleton h-40 w-full" />

        <div
            v-else-if="!activePost.activeTransportPost"
            class="card bg-base-100 min-w-full shadow-md"
        >
            <div class="card-body items-center text-center">
                <p>{{ t('active_transport_post.no_active_post') }}</p>
                <p class="text-sm opacity-70">
                    {{ t('active_transport_post.no_active_post_hint') }}
                </p>
            </div>
        </div>

        <div v-else class="card bg-base-100 min-w-full shadow-md">
            <div class="card-body">
                <div class="pb-4 text-sm opacity-70">{{ subtitle }}</div>
                <StopOverList
                    :active-transport-post="activePost.activeTransportPost"
                    :stopovers="activePost.stopovers"
                    @update:stopovers="activePost.stopovers = $event"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
