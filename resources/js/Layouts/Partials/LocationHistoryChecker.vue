<script setup lang="ts">
import { useUserStore } from '@/stores/user';
import { onMounted, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const user = useUserStore();

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
        !!user.user
    ) {
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
