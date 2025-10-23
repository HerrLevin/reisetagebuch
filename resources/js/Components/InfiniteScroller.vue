<script setup lang="ts">
import { usePage, WhenVisible } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    only: {
        type: Array,
        default: () => ['posts'],
    },
});

const onlyValue = props.only;
onlyValue.push('nextCursor');

function cursor(cursor: string | unknown) {
    if (typeof cursor !== 'string') {
        return null;
    }

    return cursor?.length > 5 ? cursor : null;
}
</script>

<template>
    <WhenVisible
        v-if="cursor(usePage().props.nextCursor) !== null"
        :always="cursor(usePage().props.nextCursor) !== null"
        :params="{
            data: {
                cursor: cursor(usePage().props.nextCursor),
            },
            only: ['posts', 'nextCursor'],
        }"
    >
        <div class="p-4 pb-8 text-center">
            <span class="loading loading-infinity loading-md"></span>
        </div>
    </WhenVisible>
    <div v-else class="p-4 pb-8 text-center">{{ t('app.scroll_end') }}</div>
</template>
