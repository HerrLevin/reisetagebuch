<script setup lang="ts">
import { api } from '@/api';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTitle } from '@/composables/useTitle';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';

const { t } = useI18n();
useTitle(t('auth.reset_password.title'));

const route = useRoute();

const form = ref({
    token: route.params.token as string,
    email: (route.query.email as string) || '',
    password: '',
    password_confirmation: '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = async () => {
    processing.value = true;
    errors.value = {};
    api.auth
        .resetPassword(form.value)
        .catch((error) => {
            if (error.response?.status === 422) {
                const responseErrors = error.response.data.errors || {};
                for (const key in responseErrors) {
                    errors.value[key] = responseErrors[key][0];
                }
            }
            form.value.password = '';
            form.value.password_confirmation = '';
        })
        .finally(() => {
            processing.value = false;
        });
};
</script>

<template>
    <AuthLayout>
        <form @submit.prevent="submit">
            <div>
                <InputLabel
                    for="email"
                    :value="t('auth.reset_password.email')"
                />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password"
                    :value="t('auth.reset_password.password')"
                />

                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    :value="t('auth.reset_password.confirm_password')"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <button
                    class="btn btn-primary"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                >
                    {{ t('auth.reset_password.title') }}
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
