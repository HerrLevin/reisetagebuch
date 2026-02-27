<script setup lang="ts">
import { api } from '@/api';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);
const recentlySuccessful = ref(false);

const updatePassword = () => {
    processing.value = true;
    errors.value = {};
    api.auth
        .updatePassword(form)
        .then(() => {
            form.current_password = '';
            form.password = '';
            form.password_confirmation = '';
            recentlySuccessful.value = true;
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
            }
            if (errors.value.password) {
                form.password = '';
                form.password_confirmation = '';
                passwordInput.value?.focus();
            }
            if (errors.value.current_password) {
                form.current_password = '';
                currentPasswordInput.value?.focus();
            }
        })
        .finally(() => {
            processing.value = false;
        });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">
                {{ t('settings.password.title') }}
            </h2>

            <p class="mt-1 text-sm opacity-65">
                {{ t('settings.password.description') }}
            </p>
        </header>

        <form class="mt-6 space-y-6" @submit.prevent="updatePassword">
            <div>
                <InputLabel
                    for="current_password"
                    :value="t('settings.password.current_password')"
                />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    :error="errors.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />

                <InputError :message="errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel
                    for="password"
                    :value="t('settings.password.new_password')"
                />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    :error="errors.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    :value="t('settings.password.confirm_password')"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :error="errors.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError
                    :message="errors.password_confirmation"
                    class="mt-2"
                />
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
