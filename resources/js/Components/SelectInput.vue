<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const props = defineProps<{
    error?: string | null;
    options: Array<{ value: string; label: string }>;
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
    <select
        ref="input"
        v-model="model"
        class="select input-bordered w-full"
        :class="{
            'input-error': props.error,
        }"
    >
        <option
            v-for="option in props.options"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>
</template>
