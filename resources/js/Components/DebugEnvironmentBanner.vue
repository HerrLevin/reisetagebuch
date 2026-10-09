<script setup lang="ts">
import { useAppConfigurationStore } from '@/stores/appConfiguration';
import { TriangleAlert, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const DISMISS_STORAGE_KEY = 'debugBannerDismissedUntil';
const DISMISS_DURATION_MS = 12 * 60 * 60 * 1000;

const { t } = useI18n();
const config = useAppConfigurationStore();
config.fetchConfig();

const dismissedUntil = ref(
    Number(localStorage.getItem(DISMISS_STORAGE_KEY) ?? 0),
);

const isDismissed = computed(() => Date.now() < dismissedUntil.value);

function dismiss() {
    const until = Date.now() + DISMISS_DURATION_MS;
    localStorage.setItem(DISMISS_STORAGE_KEY, String(until));
    dismissedUntil.value = until;
}
</script>

<template>
    <div
        v-if="config.isMisconfiguredForProduction() && !isDismissed"
        role="alert"
        class="alert alert-warning sticky top-0 z-50 justify-center rounded-none text-center"
    >
        <TriangleAlert class="h-5 w-5 shrink-0 stroke-current" />
        <span>
            {{
                t('debug_banner.message', {
                    environment: config.configuration?.environment,
                })
            }}
        </span>
        <button
            type="button"
            class="btn btn-ghost btn-xs btn-circle"
            :aria-label="t('debug_banner.dismiss')"
            @click="dismiss"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
