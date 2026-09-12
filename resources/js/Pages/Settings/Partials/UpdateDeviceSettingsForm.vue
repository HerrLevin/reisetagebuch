<script setup lang="ts">
import { useLocationHistoryStore } from '@/stores/locationHistory';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { AuthenticatedUserDto } from '../../../../types/Api.gen';

const { t } = useI18n();

defineProps<{
    user: AuthenticatedUserDto;
}>();

const { allowHistory: trackLocation } = storeToRefs(useLocationHistoryStore());
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">
                {{ t('settings.device_settings.title') }}
            </h2>

            <p class="mt-1 text-sm opacity-65">
                {{ t('settings.device_settings.description') }}
            </p>
        </header>

        <form class="mt-6 space-y-6">
            <div>
                <label>
                    <input
                        v-model="trackLocation"
                        name="trackHistory"
                        type="checkbox"
                        class="toggle"
                    />
                    {{ t('settings.device_settings.track_location_history') }}
                </label>
            </div>
        </form>
    </section>
</template>
