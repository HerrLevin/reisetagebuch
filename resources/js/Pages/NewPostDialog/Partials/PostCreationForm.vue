<script setup lang="ts">
import {
    getDescription,
    getIcon,
    getLabel,
} from '@/Services/VisibilityMapping';
import { Visibility } from '@/types/enums';
import { ref } from 'vue';

const model = defineModel({ default: '', type: String });

const props = defineProps({
    emoji: {
        type: String,
        required: true,
    },
    name: {
        type: String,
        required: true,
    },
    defaultVisibility: {
        type: String as () => Visibility | null,
        default: null,
        required: false,
    },
    subtitle: {
        type: String || null,
        default: null,
    },
    confirmButtonText: {
        type: String,
        default: 'Post',
    },
});

const emit = defineEmits(['cancel', 'selectVisibility', 'update:modelValue']);
const selectedVisibility = ref(props.defaultVisibility || Visibility.PUBLIC);

if (!props.defaultVisibility) {
    recallStoredVisibility();
}

function recallStoredVisibility() {
    const visibility = localStorage.getItem('recentVisibility');
    if (
        visibility &&
        Object.values(Visibility).includes(visibility as Visibility)
    ) {
        selectVisibility(visibility as Visibility);
    }
}

function selectVisibility(visibility: Visibility) {
    localStorage.setItem('recentVisibility', visibility);
    selectedVisibility.value = visibility;
    blur();
    emit('selectVisibility', visibility);
}

function blur() {
    (document.activeElement as HTMLElement)?.blur();
}
</script>

<template>
    <div
        class="flex w-full items-center gap-4 p-8"
        :class="{ 'pb-0': !!subtitle }"
    >
        <div class="text-3xl">{{ emoji }}</div>
        <div class="text-xl">{{ name }}</div>
    </div>
    <div v-if="subtitle" class="px-8 pb-8 text-sm opacity-70">
        {{ subtitle }}
    </div>
    <div class="bg-base-200 w-full">
        <div class="dropdown px-4 py-2">
            <div
                tabindex="0"
                role="button"
                class="btn btn-outline btn-primary btn-sm"
            >
                <component
                    :is="getIcon(selectedVisibility)"
                    class="inline size-4"
                />
                <span class="ml-2">{{ getLabel(selectedVisibility) }}</span>
            </div>
            <ul
                tabindex="-1"
                class="menu dropdown-content bg-base-100 rounded-box z-1 w-72 p-2 shadow-sm"
            >
                <template v-for="option in Visibility" :key="option.value">
                    <li class="min-w-full">
                        <a
                            :class="{
                                'bg-primary': option == selectedVisibility,
                            }"
                            @click.prevent="selectVisibility(option)"
                        >
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col">
                                    <component
                                        :is="getIcon(option)"
                                        class="inline size-4"
                                    />
                                </div>
                                <div class="col-span-11">
                                    <div class="font-bold">
                                        {{ getLabel(option) }}
                                    </div>
                                    <div class="text-sm opacity-70">
                                        {{ getDescription(option) }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                </template>
            </ul>
        </div>
        <textarea
            v-model="model"
            class="textarea textarea-ghost transparent-input w-full"
            placeholder="Statustext"
        ></textarea>
    </div>
    <div class="flex w-full justify-end gap-4 p-8"></div>
    <div class="flex w-full justify-end gap-4 px-8 py-4">
        <button class="btn btn-secondary" @click.prevent="$emit('cancel')">
            Cancel
        </button>
        <button class="btn btn-primary" type="submit">
            {{ confirmButtonText }}
        </button>
    </div>
</template>
