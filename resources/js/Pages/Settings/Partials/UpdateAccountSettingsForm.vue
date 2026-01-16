<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    status?: string;
}>();

const user = usePage().props.auth.user;
const processing = ref(false);
const recentlySuccessful = ref(false);

const form = reactive({
    motisRadius: user.settings?.motis_radius || null,
});

function formSubmit() {
    processing.value = true;
    recentlySuccessful.value = false;
    axios
        .patch('/api/account/settings', form)
        .then(() => {
            recentlySuccessful.value = true;
            setTimeout(() => {
                recentlySuccessful.value = false;
            }, 2000);
        })
        .catch((error) => {
            console.error('Error updating account settings:', error);
        })
        .finally(() => {
            processing.value = false;
        });
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">
                {{ t('settings.account_settings.title') }}
            </h2>

            <p class="mt-1 text-sm opacity-65"></p>
        </header>

        <form class="mt-6 space-y-6" @submit.prevent="formSubmit()">
            <div>
                <InputLabel
                    for="motisRadius"
                    :value="t('settings.account_settings.departure_radius')"
                />

                <SelectInput
                    id="motisRadius"
                    v-model="form.motisRadius"
                    class="mt-1 block w-full"
                    :options="[
                        {
                            value: null,
                            label: t(
                                'settings.account_settings.radius_default',
                                { meters: 500 },
                            ),
                        },
                        {
                            value: 50,
                            label: t(
                                'settings.account_settings.radius_meters',
                                { meters: 50 },
                            ),
                        },
                        {
                            value: 100,
                            label: t(
                                'settings.account_settings.radius_meters',
                                { meters: 100 },
                            ),
                        },
                        {
                            value: 200,
                            label: t(
                                'settings.account_settings.radius_meters',
                                { meters: 200 },
                            ),
                        },
                        {
                            value: 300,
                            label: t(
                                'settings.account_settings.radius_meters',
                                { meters: 300 },
                            ),
                        },
                        {
                            value: 400,
                            label: t(
                                'settings.account_settings.radius_meters',
                                { meters: 400 },
                            ),
                        },
                        {
                            value: 500,
                            label: t(
                                'settings.account_settings.radius_meters',
                                { meters: 500 },
                            ),
                        },
                    ]"
                ></SelectInput>
            </div>

            <div class="flex items-center gap-4">
                <button class="btn btn-primary" :disabled="processing">
                    {{ t('verbs.save') }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="recentlySuccessful" class="text-sm opacity-65">
                        {{ t('verbs.saved') }}
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
