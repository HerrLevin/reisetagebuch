<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const props = defineProps<{
    error?: string | null;
}>();

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="input input-bordered w-full"
        :class="{
           'input-error': props.error,
        }"
        v-model="model"
        ref="input"
    />
</template>
