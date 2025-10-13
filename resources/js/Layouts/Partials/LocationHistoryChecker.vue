<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onMounted, useTemplateRef } from 'vue';

const deleteModal = useTemplateRef('confirmLocationTrackingModal');

const maxAge = 60 * 60 * 24 * 365; // 1 year

function track() {
    document.cookie = 'rtb_allow_history=true; path=/; max-age=' + maxAge;
    deleteModal.value?.close();
}

function dontTrack() {
    document.cookie = 'rtb_disallow_history=true; path=/; max-age=' + maxAge;
    deleteModal.value?.close();
}

onMounted(() => {
    if (
        !document.cookie.includes('rtb_allow_history') &&
        !document.cookie.includes('rtb_disallow_history') &&
        !!usePage().props.auth.user
    ) {
        deleteModal.value?.showModal();
    }
});
</script>

<template>
    <dialog ref="confirmLocationTrackingModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Do you want to track your location history with this device?
            </h3>
            <p class="py-4">
                This will allow you to see your location history on the map.
                You'll be the only one who can see this information. You can
                change this setting at any time in your account settings.
            </p>

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn" @click.prevent="dontTrack()">
                        Don't track
                    </button>
                </form>

                <button class="btn btn-primary" @click.prevent="track()">
                    Track location
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</template>
