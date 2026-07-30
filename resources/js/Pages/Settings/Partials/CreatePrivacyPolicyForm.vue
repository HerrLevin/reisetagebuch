<script setup lang="ts">
import { api } from '@/api';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { PrivacyPolicyDto } from '../../../../types/Api.gen';

const { t } = useI18n();

const processing = ref(false);
const recentlySuccessful = ref(false);
const currentPolicy = ref<PrivacyPolicyDto | null>(null);
const upcomingPolicy = ref<PrivacyPolicyDto | null>(null);
const errors = ref<Record<string, string>>({});
const generalError = ref<string | null>(null);

const form = reactive<{ content: string; validFrom: string }>({
    content: '',
    validFrom: '',
});

function formatDate(value: string): string {
    return new Date(value).toLocaleString();
}

function loadOverview() {
    api.app
        .getPrivacyPolicy()
        .then((response) => {
            currentPolicy.value = response.data;
        })
        .catch(() => {
            currentPolicy.value = null;
        });

    api.app
        .getUpcomingPrivacyPolicy()
        .then((response) => {
            upcomingPolicy.value = response.data;
        })
        .catch(() => {
            upcomingPolicy.value = null;
        });
}

onMounted(() => {
    loadOverview();
});

function formSubmit() {
    processing.value = true;
    recentlySuccessful.value = false;
    errors.value = {};
    generalError.value = null;
    api.app
        .createPrivacyPolicy({
            content: form.content,
            validFrom: new Date(form.validFrom).toISOString(),
        })
        .then(() => {
            form.content = '';
            form.validFrom = '';
            recentlySuccessful.value = true;
            loadOverview();
            setTimeout(() => {
                recentlySuccessful.value = false;
            }, 2000);
        })
        .catch((error) => {
            if (error.response?.data?.errors) {
                errors.value = Object.fromEntries(
                    Object.entries(error.response.data.errors).map(
                        ([key, val]) => [key, (val as string[])[0]],
                    ),
                );
            } else {
                generalError.value =
                    error.response?.data?.message ||
                    t('settings.privacyPolicy.error');
            }
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
                {{ t('settings.privacyPolicy.title') }}
            </h2>

            <p class="mt-1 text-sm opacity-65">
                {{ t('settings.privacyPolicy.description') }}
            </p>

            <p class="mt-2 text-sm opacity-65">
                {{
                    currentPolicy
                        ? t('settings.privacyPolicy.currentVersion', {
                              date: formatDate(currentPolicy.validFrom),
                          })
                        : t('settings.privacyPolicy.noCurrentVersion')
                }}
            </p>
            <p class="text-sm opacity-65">
                {{
                    upcomingPolicy
                        ? t('settings.privacyPolicy.upcomingVersion', {
                              date: formatDate(upcomingPolicy.validFrom),
                          })
                        : t('settings.privacyPolicy.noUpcomingVersion')
                }}
            </p>
        </header>

        <form class="mt-6 space-y-6" @submit.prevent="formSubmit()">
            <InputError :message="generalError ?? undefined" />

            <div>
                <InputLabel
                    for="privacyPolicyContent"
                    :value="t('settings.privacyPolicy.content')"
                />

                <textarea
                    id="privacyPolicyContent"
                    v-model="form.content"
                    class="textarea mt-1 block min-h-48 w-full"
                    required
                ></textarea>

                <InputError :message="errors.content" class="mt-2" />
            </div>

            <div>
                <InputLabel
                    for="privacyPolicyValidFrom"
                    :value="t('settings.privacyPolicy.validFrom')"
                />

                <input
                    id="privacyPolicyValidFrom"
                    v-model="form.validFrom"
                    type="datetime-local"
                    class="input mt-1 block w-full"
                    required
                />

                <InputError :message="errors.validFrom" class="mt-2" />
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
