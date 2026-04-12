<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTitle } from '@/composables/useTitle';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAppConfigurationStore } from '@/stores/appConfiguration';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import { Info } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink, useRouter } from 'vue-router';

const { t } = useI18n();
useTitle(t('auth.login.title'));

const router = useRouter();
const authStore = useAuthStore();
const userStore = useUserStore();

const form = ref({
    email: '',
    password: '',
    remember: false,
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);
const status = ref('');

const config = useAppConfigurationStore();
config.fetchConfig();

const submit = async () => {
    processing.value = true;
    errors.value = {};
    try {
        await authStore.login(form.value.email, form.value.password);
        await userStore.fetchUser(true);
        router.push({ name: 'home' });
    } catch (error) {
        console.error(error);
        if (error.response?.status === 422) {
            const responseErrors = error.response.data.errors || {};
            for (const key in responseErrors) {
                errors.value[key] = responseErrors[key][0];
            }
        } else {
            errors.value.email =
                error.response?.data?.message || t('auth.login.failed');
        }
        form.value.password = '';
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <AuthLayout>
        <h2 class="mb-2 text-center text-2xl font-semibold">
            {{ t('auth.login.title') }}
        </h2>
        <div v-if="status" class="text-success mb-4 text-sm font-medium">
            {{ status }}
        </div>
        <div
            v-if="router.currentRoute.value.query.loggedOut === 'true'"
            class="alert alert-info my-4"
        >
            <Info class="size-4" />
            {{ t('auth.logged_out') }}
        </div>

        <form @submit.prevent="submit">
            <div class="form-control w-full">
                <InputLabel for="email" :value="t('auth.login.email')" />

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

            <div class="form-control mt-4 w-full">
                <InputLabel for="password" :value="t('auth.login.password')" />

                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    :error="errors.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="errors.password" />
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

                <RouterLink to="/forgot-password" class="link">
                    {{ t('auth.login.forgot_password') }}
                </RouterLink>
            </div>

            <button
                type="submit"
                class="btn btn-primary mt-12 w-full"
                :class="{ 'opacity-25': processing }"
                :disabled="processing"
            >
                {{ t('auth.login.title') }}
            </button>
        </form>
        <div v-if="config.canRegister()" class="mt-4 text-center">
            {{ t('auth.login.no_account') }}
            <RouterLink to="/register" class="link">
                {{ t('auth.register.title') }}
            </RouterLink>
        </div>
        <div v-else class="mt-4 text-center">
            {{ t('auth.login.no_register') }}
        </div>
    </AuthLayout>
</template>
