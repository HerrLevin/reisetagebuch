<script setup lang="ts">
import { PropType, ref } from 'vue';

const model = defineModel<string>({ required: true });

defineProps({
    name: {
        type: String,
        default: '',
    },
    errors: {
        type: Array,
        default: () => [],
    },
    suggestions: {
        type: Array as PropType<{ label: string; value: any }[]>,
        default: () => [],
    },
});

defineEmits(['submit', 'select', 'update:modelValue']);

const loading = ref(false);
</script>

<template>
    <div class="dropdown w-full">
        <input
            type="text"
            tabindex="0"
            class="input input-bordered w-full"
            v-model="model"
            :class="{ 'input-error': errors.length }"
            :name="name"
            @keydown="$emit('update:modelValue', model)"
            @keydown.enter="$emit('submit')"
        />
        <ul
            tabindex="0"
            class="dropdown-content menu bg-base-100 rounded-box z-1 w-full p-2 shadow-sm"
        >
            <li v-for="(suggestion, index) in suggestions" :key="index">
                <a @click="$emit('select', suggestion)">
                    {{ suggestion.label }}
                </a>
            </li>
            <div v-if="!suggestions.length">
                <span class="opacity-60">
                    No suggestions available. Please type to search.
                </span>
            </div>
        </ul>
    </div>
    <div v-if="errors.length" class="invalid-feedback">
        <span v-for="(error, index) in errors" :key="index">{{ error }}</span>
    </div>
    <div v-if="loading" class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <ul v-if="suggestions.length" class="list-group mt-2">
        <li
            v-for="(suggestion, index) in suggestions"
            :key="index"
            class="list-group-item list-group-item-action"
            @click="$emit('select', suggestion)"
        >
            {{ suggestion.label }}
        </li>
    </ul>
</template>

<style scoped></style>
