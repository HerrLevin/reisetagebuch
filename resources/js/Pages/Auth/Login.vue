<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAppConfigurationStore } from '@/stores/appConfiguration';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const config = useAppConfigurationStore();
config.fetchConfig();

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head :title="t('auth.login.title')" />

        <h2 class="mb-2 text-center text-2xl font-semibold">
            {{ t('auth.login.title') }}
        </h2>
        <div v-if="status" class="text-success mb-4 text-sm font-medium">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="form-control w-full">
                <InputLabel for="email" :value="t('auth.login.email')" />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    :error="form.errors.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="form-control mt-4 w-full">
                <InputLabel for="password" :value="t('auth.login.password')" />

                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    :error="form.errors.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <fieldset class="form-control">
                    <label class="fieldset-label">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="checkbox"
                        />
                        {{ t('auth.login.remember_me') }}
                    </label>
                </fieldset>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="link"
                >
                    {{ t('auth.login.forgot_password') }}
                </Link>
            </div>

            <button
                class="btn btn-primary mt-12 w-full"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                {{ t('auth.login.title') }}
            </button>
        </form>
        <div v-if="config.canRegister()" class="mt-4 text-center">
            {{ t('auth.login.no_account') }}
            <Link :href="route('register')" class="link">
                {{ t('auth.register.title') }}
            </Link>
        </div>
        <div v-else class="mt-4 text-center">
            {{ t('auth.login.no_register') }}
        </div>
    </AuthLayout>
</template>
