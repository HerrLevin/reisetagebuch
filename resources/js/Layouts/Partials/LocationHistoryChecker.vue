<script setup lang="ts">
import { useLocationHistoryStore } from '@/stores/locationHistory';
import { useUserStore } from '@/stores/user';
import { onMounted, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const user = useUserStore();
const locationHistory = useLocationHistoryStore();

const deleteModal = useTemplateRef('confirmLocationTrackingModal');

function track() {
    locationHistory.setAllowHistory(true);
    deleteModal.value?.close();
}

function dontTrack() {
    locationHistory.setAllowHistory(false);
    deleteModal.value?.close();
}

onMounted(() => {
    if (locationHistory.allowHistory === null && !!user.user) {
        deleteModal.value?.showModal();
    }
});
</script>

<template>
    <dialog ref="confirmLocationTrackingModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                {{ t('history_checker.title') }}
            </h3>
            <p class="py-4">
                {{ t('history_checker.message') }}
            </p>

            <div class="modal-action">
                <form method="dialog">
                    <button class="btn" @click.prevent="dontTrack()">
                        {{ t('history_checker.decline') }}
                    </button>
                </form>

                <button class="btn btn-primary" @click.prevent="track()">
                    {{ t('history_checker.accept') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>{{ t('verbs.close') }}</button>
        </form>
    </dialog>
</template>
