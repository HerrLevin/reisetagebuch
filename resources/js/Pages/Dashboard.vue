<script setup lang="ts">
import Loading from '@/Components/Loading.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Timeline from '@/Pages/Dashboard/Timeline.vue';
import { BasePost, LocationPost, TransportPost } from '@/types/PostTypes';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const posts = ref<Array<BasePost | TransportPost | LocationPost>>([]);
const loading = ref(false);
const nextCursor = ref<string | null>(null);

loading.value = true;
axios.get('/api/timeline').then((response) => {
    posts.value = response.data.items;
    nextCursor.value = response.data.nextCursor;
    loading.value = false;
});

function loadMore() {
    if (loading.value || !nextCursor.value) {
        return;
    }

    loading.value = true;
    axios
        .get('/api/timeline', {
            params: {
                cursor: nextCursor.value,
            },
        })
        .then((response) => {
            posts.value.push(...response.data.items);
            if (response.data.nextCursor === nextCursor.value) {
                nextCursor.value = null;
                return;
            }
            nextCursor.value = response.data.nextCursor;
        })
        .finally(() => {
            loading.value = false;
        });
}
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
            <Timeline
                :posts
                :show-next="!loading && !!nextCursor"
                @next="loadMore()"
            />
            <Loading v-show="loading" class="m-4 mx-auto" />
        </div>
    </AuthenticatedLayout>
</template>
