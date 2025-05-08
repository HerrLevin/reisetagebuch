<script setup lang="ts">
import { usePage, WhenVisible } from '@inertiajs/vue3';

const props = defineProps({
    only: {
        type: Array,
        default: () => ['posts'],
    },
});
const page = usePage();

const onlyValue = props.only;
onlyValue.push('nextCursor');
</script>

<template>
    <WhenVisible
        :always="page.props.nextCursor !== null"
        :params="{
            data: {
                cursor:
                    typeof page.props.nextCursor === 'string'
                        ? page.props.nextCursor
                        : null,
            },
            only: ['posts', 'nextCursor'],
        }"
    >
        <div v-if="page.props.nextCursor === null" class="p-4 pb-8 text-center">
            You've reached the end!
        </div>
        <div v-else class="p-4 pb-8 text-center">
            <span class="loading loading-infinity loading-md"></span>
        </div>
    </WhenVisible>
</template>

<style scoped></style>
