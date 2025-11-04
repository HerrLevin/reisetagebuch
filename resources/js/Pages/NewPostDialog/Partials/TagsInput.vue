<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const tagsModel = ref<string[]>([]);
const tagsInput = ref<string>('');
const highlighted = ref<boolean>(false);
const emit = defineEmits<{
    (e: 'update:tags', value: string[]): void;
}>();

const props = defineProps<{
    tags: string[];
}>();

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
</script>
<template>
    <label class="input input-ghost w-full">
        <span
            v-for="(tag, key) in tagsModel"
            :key="key"
            class="badge badge-secondary pe-0"
            :class="{
                'badge-soft': !(highlighted && key === tagsModel.length - 1),
            }"
        >
            #{{ tag }}
            <button
                class="btn btn-xs btn-circle btn-ghost"
                @click.prevent="removeTag(tag)"
            >
                <X class="size-3" />
            </button>
        </span>
        <input
            v-model="tagsInput"
            type="text"
            class="grow"
            :disabled="tagsModel.length >= 5"
            :placeholder="t('new_post.tags_placeholder')"
            @keydown.enter.prevent="pushTag()"
            @keydown.backspace.prevent="deleteRecentTag()"
        />
    </label>
</template>
