<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const tags = ref<string[]>([]);
const tagsInput = ref<string>('');
const tagsInputRef = useTemplateRef('tagsInputRef');
const tagModal = useTemplateRef('tagModal');

function pushTag() {
    tagsInput.value = tagsInput.value.replaceAll(/[^0-9a-zA-Z_ ]/g, '');
    tags.value.push(tagsInput.value);
    tagsInput.value = '';
}
</script>
<template>
    <div class="flex flex-wrap p-2">
        <div class="flex flex-wrap gap-2">
            <div
                v-for="(tag, key) in tags"
                :key="key"
                class="badge badge-soft badge-secondary"
            >
                #{{ tag }}
            </div>

            <dialog ref="tagModal" class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold">
                        {{ t('new_post.tags_placeholder') }}
                    </h3>
                    <div class="flex flex-wrap gap-2 p-2">
                        <div
                            v-for="(tag, id) in tags"
                            :key="id"
                            class="badge badge-secondary badge-soft pe-0"
                        >
                            #
                            {{ tag }}
                            <button
                                class="btn btn-xs btn-circle btn-ghost"
                                @click="tags.splice(id, 1)"
                            >
                                <X class="size-3" />
                            </button>
                        </div>
                    </div>
                    <input
                        ref="tagsInputRef"
                        v-model="tagsInput"
                        autofocus
                        :placeholder="t('new_post.tags_placeholder')"
                        class="input input-xs input-ghost w-fill"
                        type="text"
                        @keydown.enter.prevent="pushTag()"
                    />

                    <div class="modal-action">
                        <form method="dialog">
                            <button
                                class="btn"
                                @click.prevent="tagModal?.close()"
                            >
                                {{ t('verbs.close') }}
                            </button>
                        </form>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>{{ t('verbs.close') }}</button>
                </form>
            </dialog>
            <button
                class="badge badge-outline cursor-pointer"
                @click.prevent="
                    tagModal?.showModal();
                    tagsInputRef?.focus();
                "
            >
                #<span class="opacity-75">
                    {{ t('new_post.tags_placeholder') }}
                </span>
            </button>
        </div>
        <div class="flex flex-wrap gap-2"></div>
    </div>
</template>
