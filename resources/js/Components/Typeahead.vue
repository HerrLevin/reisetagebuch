<script setup lang="ts">
import { CircleX } from 'lucide-vue-next';
import { PropType, ref } from 'vue';

const model = defineModel<string>({ required: true });

export type Suggestion = {
    label: string;
    value: object | string;
    subLabel: string | undefined;
};

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
        type: Array as PropType<Suggestion[]>,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: '',
    },
});

defineEmits(['submit', 'select', 'update:modelValue', 'focus']);

const loading = ref(false);

function blur() {
    (document.activeElement as HTMLElement)?.blur();
}
</script>

<template>
    <div class="dropdown w-full">
        <label class="input input-bordered w-full">
            <input
                v-model="model"
                :placeholder="placeholder"
                type="text"
                tabindex="0"
                class="w-full"
                :class="{ 'input-error': errors.length }"
                :name="name"
                @focusin="$emit('focus')"
                @keydown="$emit('update:modelValue', model)"
                @keydown.enter="
                    $emit('submit');
                    blur();
                "
            />
            <CircleX
                v-if="model"
                class="h-[1.5em] cursor-pointer"
                @click="model = ''"
            />
        </label>
        <ul
            tabindex="0"
            class="dropdown-content menu bg-base-100 rounded-box z-1 w-full p-2 shadow-sm"
        >
            <li
                v-for="(suggestion, index) in suggestions"
                :key="index"
                class="max-w-full"
            >
                <a
                    class="inline-block w-full"
                    @click="
                        $emit('select', suggestion);
                        blur();
                    "
                >
                    <h3 class="truncate font-bold">
                        {{ suggestion.label }}
                    </h3>
                    <h6
                        v-if="suggestion.subLabel"
                        class="truncate text-xs opacity-60"
                    >
                        {{ suggestion.subLabel }}
                    </h6>
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
</template>
