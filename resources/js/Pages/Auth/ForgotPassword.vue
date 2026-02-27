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
useTitle('Forgot Password');

const form = ref({ email: '' });
const errors = ref<Record<string, string>>({});
const processing = ref(false);
const status = ref('');

const submit = async () => {
    processing.value = true;
    errors.value = {};
    status.value = '';
    api.auth
        .forgotPassword(form.value)
        .catch((error) => {
            if (error.response?.status === 422) {
                const responseErrors = error.response.data.errors || {};
                for (const key in responseErrors) {
                    errors.value[key] = responseErrors[key][0];
                }
            }
        })
        .finally(() => {
            processing.value = false;
        });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-4 text-sm">
            {{ t('auth.forgot_password.message') }}
        </div>

        <div v-if="status" class="text-success mb-4 text-sm font-medium">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel
                    for="email"
                    :value="t('auth.forgot_password.email')"
                />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    :error="errors.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <button
                    class="btn btn-primary"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                >
                    {{ t('auth.forgot_password.confirm') }}
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
