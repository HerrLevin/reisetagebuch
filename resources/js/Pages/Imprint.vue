<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import markdownit from 'markdown-it';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
useTitle(t('pages.imprint.title'));

const content = ref<string | null>(null);
const loaded = ref(false);
const md = markdownit();

onMounted(() => {
    api.app
        .getImprint()
        .then((response) => {
            content.value = response.data.content
                ? md.render(response.data.content)
                : null;
        })
        .finally(() => {
            loaded.value = true;
        });
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('pages.imprint.title') }}
            </h2>
        </template>

        <div class="card bg-base-100 min-w-full p-8 shadow-md">
            <h1 class="mb-4 text-2xl font-semibold">
                {{ t('pages.imprint.title') }}
            </h1>

            <!-- eslint-disable-next-line vue/no-v-html -->
            <pre v-if="loaded && content" class="prose" v-html="content"></pre>

            <p v-else-if="loaded" class="opacity-65">
                {{ t('pages.imprint.empty') }}
            </p>
        </div>
    </AuthenticatedLayout>
</template>
