<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const tagsModel = ref<string[]>([]);
const tagsInput = ref<string>('');
const highlighted = ref<boolean>(false);
const emit = defineEmits<{
    (e: 'update:tags', value: string[]): void;
}>();

const props = defineProps({
    tags: {
        type: Array as () => string[],
        default: () => [],
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

tagsModel.value = props.tags;

function pushTag() {
    if (tagsModel.value.length >= 5 || tagsInput.value.trim() === '') {
        return;
    }
    tagsInput.value = tagsInput.value.replaceAll(/[^0-9a-zA-Z_ ]/g, '');
    tagsModel.value.push(tagsInput.value);
    tagsInput.value = '';
    emit('update:tags', tagsModel.value);
}

function removeTag(value: string) {
    const index = tagsModel.value.indexOf(value);
    if (index === -1) {
        return;
    }
    tagsModel.value.splice(index, 1);
    emit('update:tags', tagsModel.value);
}

function deleteRecentTag() {
    if (tagsInput.value === '') {
        const index = tagsModel.value.length - 1;
        if (index === -1) {
            return;
        }
        if (highlighted.value) {
            removeTag(tagsModel.value[index]);
            highlighted.value = false;
            return;
        }
        highlighted.value = true;
    }
}

watch(
    () => props.tags,
    (newTags) => {
        tagsModel.value = newTags;
    },
    { immediate: true },
);
</script>
<template>
    <label
        class="textarea-ghost w-full"
        :class="{ 'pointer-events-none opacity-50': disabled }"
    >
        <span
            v-for="(tag, key) in tagsModel"
            :key="key"
            class="badge badge-secondary mx-1 pe-0"
            :class="{
                'badge-soft': !(highlighted && key === tagsModel.length - 1),
            }"
        >
            #{{ tag }}
            <button
                class="btn btn-xs btn-circle btn-ghost"
                :disabled="disabled"
                @click.prevent="removeTag(tag)"
            >
                <X class="size-3" />
            </button>
        </span>

        <input
            v-model="tagsInput"
            type="text"
            class="tag-input input input-ghost grow"
            :disabled="tagsModel.length >= 5"
            :placeholder="t('new_post.tags_placeholder')"
            @keydown.enter.prevent="pushTag()"
            @keydown.backspace="deleteRecentTag()"
        />
    </label>
</template>
<style scoped>
.tag-input input {
    border: none;
    box-shadow: none;
    outline: none;
    width: auto;
    min-width: 5rem;
}

input.tag-input:focus {
    border: none;
    box-shadow: none;
    outline: none !important;
}
.textarea-ghost {
    &:focus,
    &:focus-within {
        box-shadow: none !important;
        border-color: transparent !important;
        background-color: transparent !important;
    }
}
.tag-input {
    &:focus,
    &:focus-within {
        box-shadow: none !important;
        border-color: transparent !important;
        background-color: transparent !important;
    }
}
</style>
