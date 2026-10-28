<script setup lang="ts">
import { useTitle } from '@/composables/useTitle';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { isNative } from '@/Services/ServerConfig';
import { startNativeLogin } from '@/Services/OAuth';
import { useAuthStore } from '@/stores/auth';
import { Info } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';

const { t } = useI18n();
useTitle(t('auth.login.title'));

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

async function login() {
    const result = await startNativeLogin();
    if (result) {
        authStore.setToken(result.token, result.expiresAt);
        router.push({ name: 'home' });
    }
}

// Web: /login and /register are server-rendered; reload to get them.
if (!isNative()) {
    window.location.replace(route.fullPath);
}

// Native: right after connecting a server, go straight to the sign-in dialog.
if (isNative() && route.query.start === 'true') {
    login();
}
</script>

<template>
    <AuthLayout v-if="isNative()">
        <h2 class="mb-2 text-center text-2xl font-semibold">
            {{ t('auth.login.title') }}
        </h2>
        <div
            v-if="route.query.loggedOut === 'true'"
            class="alert alert-info my-4"
        >
            <Info class="size-4" />
            {{ t('auth.logged_out') }}
        </div>
        <button
            type="button"
            class="btn btn-primary mt-12 w-full"
            @click="login()"
        >
            {{ t('auth.login.title') }}
        </button>
    </AuthLayout>
</template>
