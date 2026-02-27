<script setup lang="ts">
import { api } from '@/api';
import { useTitle } from '@/composables/useTitle';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const { t } = useI18n();
useTitle(t('auth.verify_email.title'));

const router = useRouter();
const authStore = useAuthStore();
const userStore = useUserStore();

const processing = ref(false);
const verificationLinkSent = ref(false);

const submit = async () => {
    processing.value = true;
    api.auth
        .resendVerificationEmail()
        .then((response) => {
            if (response.status === 200) {
                verificationLinkSent.value = true;
            }
        })
        .catch(() => {
            // ignore
        })
        .finally(() => {
            processing.value = false;
        });
};

const logout = async () => {
    await authStore.logout();
    userStore.invalidateUser();
    router.push({ name: 'login' });
};
</script>

<template>
    <AuthLayout>
        <div class="mb-4 text-sm">
            {{ t('auth.verify_email.message') }}
        </div>

        <div
            v-if="verificationLinkSent"
            class="text-success mb-4 text-sm font-medium"
        >
            {{ t('auth.verify_email.link_sent') }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <button
                    class="btn btn-primary"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                >
                    {{ t('auth.verify_email.resend') }}
                </button>

                <button type="button" class="btn btn-link" @click="logout">
                    {{ t('auth.verify_email.logout') }}
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
