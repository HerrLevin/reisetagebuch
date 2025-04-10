<script setup lang="ts">
import { defineProps, ref } from 'vue';
import { LocationEntry } from '@/types';

defineProps({
    location: {
        type: Object,
        default: () => ({}) as LocationEntry,
    },
    showStartButton: {
        type: Boolean,
        default: true,
    },
    data: {
        type: Object,
        default: () => ({}),
    },
});

const tagModal = ref<HTMLDialogElement | null>(null);
const modalBox = ref<HTMLElement | null>(null);

function closeModal() {
    tagModal.value?.close();
}

function openModal() {
    tagModal.value?.showModal();
    if (modalBox.value) {
        modalBox.value.scrollTop = 0;
    }
}
</script>

<template>
    <slot name="activator" :onClick="openModal" />
    <dialog
        ref="tagModal"
        class="modal modal-bottom sm:modal-middle"
        @click.prevent=""
    >
        <div class="modal-box p-0" ref="modalBox">
            <ul class="list">
                <li class="text p-4 pb-2 tracking-wide opacity-60">
                    {{ location.name }}
                </li>

                <li
                    class="list-row py-3"
                    v-for="tag in location.tags"
                    :key="tag.key"
                >
                    <div>
                        <div class="text-xs opacity-60">{{ tag.key }}</div>
                        <div class="font-semibold">{{ tag.value }}</div>
                    </div>
                </li>
            </ul>

            <div class="modal-action m-4">
                <button class="btn" @click.prevent="closeModal()">Close</button>
            </div>
        </div>

        <form
            method="dialog"
            class="modal-backdrop"
            @click.prevent="closeModal()"
        >
            <button>close</button>
        </form>
    </dialog>
</template>
