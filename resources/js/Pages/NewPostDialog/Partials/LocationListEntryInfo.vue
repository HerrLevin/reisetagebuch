<script setup lang="ts">
import { defineProps, ref } from 'vue';
import { LocationEntry, LocationIdentifier } from '@/types';
import { ExternalLink } from 'lucide-vue-next';

const props = defineProps({
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

const osmIdentifier = ref<LocationIdentifier | null>(null);

osmIdentifier.value = props.location.identifiers.find(
    (id: LocationIdentifier) => id.origin === 'osm',
);

function getOsmLink(identifier: LocationIdentifier): string {
    return `https://www.openstreetmap.org/${identifier.type}/${identifier.identifier}`;
}

function openOsmLink() {
    if (osmIdentifier.value) {
        window.open(getOsmLink(osmIdentifier.value), '_blank');
    }
}

function closeModal() {
    console.log('closeModal');
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
    <slot name="activator" :on-click="openModal" />
    <dialog
        ref="tagModal"
        class="modal modal-bottom sm:modal-middle"
        @click.prevent=""
    >
        <div ref="modalBox" class="modal-box p-0">
            <ul class="list">
                <li class="text p-4 pb-2 text-center tracking-wide opacity-60">
                    {{ location.name }}
                    <button
                        class="btn btn-sm btn-circle btn-ghost absolute top-2 right-2"
                        @click="closeModal()"
                    >
                        ✕
                    </button>
                </li>

                <li
                    v-for="tag in location.tags"
                    :key="tag.key"
                    class="list-row py-3"
                >
                    <div>
                        <div class="text-xs opacity-60">{{ tag.key }}</div>
                        <div class="font-semibold">{{ tag.value }}</div>
                    </div>
                </li>
            </ul>

            <div class="modal-action m-4">
                <a
                    v-if="osmIdentifier"
                    :href="getOsmLink(osmIdentifier)"
                    target="_blank"
                    class="btn btn-outline"
                    @click.prevent="openOsmLink()"
                >
                    Open in OSM<ExternalLink class="ml-2 inline size-4" />
                </a>
                <button class="btn btn-primary" @click.prevent="closeModal()">
                    Close
                </button>
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
