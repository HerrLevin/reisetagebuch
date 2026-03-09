<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useTitle } from '@/composables/useTitle';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink, useRoute, useRouter } from 'vue-router';

const { t } = useI18n();
useTitle(t('auth.register.title'));

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const userStore = useUserStore();

const inviteCode = (route.query.invite as string) || null;
const invitedBy = ref<string | null>(null);

// Validate invite code if present
if (inviteCode) {
    fetch(`/api/auth/invite/${inviteCode}`)
        .then((r) => r.json())
        .then((data) => {
            if (data.valid) {
                invitedBy.value = data.invitedBy;
            }
        })
        .catch(() => {});
}

const form = ref({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    invite: inviteCode || '',
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);

const submit = async () => {
    processing.value = true;
    errors.value = {};
    try {
        await authStore.register({
            name: form.value.name,
            username: form.value.username,
            email: form.value.email,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            invite: form.value.invite || undefined,
        });
        await userStore.fetchUser(true);
        router.push({ name: 'home' });
    } catch (error) {
        if (error.response?.status === 422) {
            const responseErrors = error.response.data.errors || {};
            for (const key in responseErrors) {
                errors.value[key] = responseErrors[key][0];
            }
        }
        form.value.password = '';
        form.value.password_confirmation = '';
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <AuthLayout>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" :value="t('auth.register.name')" />

                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    :error="errors.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div>
                <InputLabel
                    for="username"
                    :value="t('auth.register.username')"
                />

                <TextInput
                    id="username"
                    v-model="form.username"
                    type="text"
                    class="mt-1 block w-full"
                    :error="errors.username"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="errors.username" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" :value="t('auth.register.email')" />

                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password"
                    :value="t('auth.register.password')"
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
                    :value="t('auth.register.confirm_password')"
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
                <InputError class="mt-2" :message="errors.invite" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <RouterLink to="/login" class="link">
                    {{ t('auth.register.have_account') }}
                </RouterLink>

                <button
                    class="btn btn-primary ms-4"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                >
                    {{ t('auth.register.title') }}
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
