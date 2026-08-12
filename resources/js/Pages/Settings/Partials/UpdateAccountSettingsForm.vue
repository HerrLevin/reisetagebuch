<script setup lang="ts">
import { api } from '@/api';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { AuthenticatedUserDto } from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps<{
    user: AuthenticatedUserDto;
}>();

const processing = ref(false);
const recentlySuccessful = ref(false);

const form = reactive({
    motisRadius: props.user.settings.motisRadius as
        | 500
        | 50
        | 100
        | 200
        | 300
        | 400
        | null,
    requiresFollowRequest: props.user.settings.requiresFollowRequest,
    hidePostsAfter: props.user.settings.hidePostsAfter as
        | 1
        | 2
        | 3
        | 4
        | 5
        | 6
        | 7
        | 14
        | 0.25
        | 0.5
        | 30
        | null,
});

function formSubmit() {
    processing.value = true;
    recentlySuccessful.value = false;
    api.account
        .updateSettings(form)
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
            <div>
                <InputLabel
                    for="hidePostsAfter"
                    :value="t('settings.account_settings.hide_after_days')"
                />

                <SelectInput
                    id="hidePostsAfter"
                    v-model="form.hidePostsAfter"
                    class="mt-1 block w-full"
                    :options="[
                        {
                            value: null,
                            label: t('settings.account_settings.hide_default'),
                        },
                        {
                            value: 0.25,
                            label: t('settings.account_settings.hide_6h'),
                        },
                        {
                            value: 0.5,
                            label: t('settings.account_settings.hide_12h'),
                        },
                        {
                            value: 1,
                            label: t('settings.account_settings.hide_1d'),
                        },
                        {
                            value: 2,
                            label: t('settings.account_settings.hide_days', {
                                days: 2,
                            }),
                        },
                        {
                            value: 3,
                            label: t('settings.account_settings.hide_days', {
                                days: 3,
                            }),
                        },
                        {
                            value: 4,
                            label: t('settings.account_settings.hide_days', {
                                days: 4,
                            }),
                        },
                        {
                            value: 5,
                            label: t('settings.account_settings.hide_days', {
                                days: 5,
                            }),
                        },
                        {
                            value: 6,
                            label: t('settings.account_settings.hide_days', {
                                days: 6,
                            }),
                        },
                        {
                            value: 7,
                            label: t('settings.account_settings.hide_days', {
                                days: 7,
                            }),
                        },
                        {
                            value: 14,
                            label: t('settings.account_settings.hide_days', {
                                days: 14,
                            }),
                        },
                        {
                            value: 32,
                            label: t('settings.account_settings.hide_days', {
                                days: 32,
                            }),
                        },
                    ]"
                ></SelectInput>
            </div>

            <div>
                <label>
                    <input
                        v-model="form.requiresFollowRequest"
                        name="requiresFollowRequest"
                        type="checkbox"
                        class="toggle"
                    />
                    {{ t('settings.account_settings.requires_follow_request') }}
                </label>
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
