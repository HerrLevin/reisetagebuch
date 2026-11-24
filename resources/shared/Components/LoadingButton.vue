<script setup lang="ts">
import { computed, ref, type Component } from 'vue';

// Either pass `action` (returning the request's promise) and the button tracks it,
// or pass `loading` when the parent already tracks the request, e.g. a form submit.
const props = withDefaults(
    defineProps<{
        action?: () => unknown;
        loading?: boolean;
        disabled?: boolean;
        type?: 'button' | 'submit';
        icon?: Component;
        iconClass?: string;
    }>(),
    { action: undefined, type: 'button', icon: undefined, iconClass: 'size-4' },
);

const running = ref(false);
const busy = computed(() => running.value || props.loading);

async function run() {
    if (!props.action || busy.value) {
        return;
    }
    running.value = true;
    try {
        await props.action();
    } finally {
        running.value = false;
    }
}
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || busy"
        :aria-busy="busy"
        @click="run"
    >
        <span
            v-if="busy"
            class="loading loading-spinner"
            :class="iconClass"
            aria-hidden="true"
        />
        <component :is="icon" v-else-if="icon" :class="iconClass" />
        <slot />
    </button>
</template>
