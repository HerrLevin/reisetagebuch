<script setup lang="ts">
import { useTitle } from '@shared/composables/useTitle';

const props = defineProps<{ title: string; description?: string }>();
useTitle(props.title);
</script>

<template>
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ title }}</h1>
            <p v-if="description" class="mt-1 text-sm opacity-65">
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.actions" class="flex gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
