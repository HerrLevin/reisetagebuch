<script setup lang="ts">
import { api } from '@/api';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTitle } from '@/composables/useTitle';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
useTitle(t('auth.confirm_password_title'));

const form = ref({ password: '' });
const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = async () => {
    processing.value = true;
    errors.value = {};
    await api.auth
        .updatePassword({
            current_password: form.value.password,
            password: form.value.password,
            password_confirmation: form.value.password,
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                const responseErrors = error.response.data.errors || {};
                for (const key in responseErrors) {
                    errors.value[key] = responseErrors[key][0];
                }
            }
            form.value.password = '';
        })
        .finally(() => {
            processing.value = false;
            form.value.password = '';
        });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-4 text-sm">
            {{ t('auth.confirm_password') }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" :value="t('auth.password')" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    :error="errors.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="errors.password" />
            </div>

            <div class="mt-4 flex justify-end">
                <button
                    class="btn btn-primary ms-4"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                >
                    {{ t('verbs.confirm') }}
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
