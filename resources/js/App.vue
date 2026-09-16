<script setup lang="ts">
import Forbidden from '@/Pages/Errors/Forbidden.vue';
import NotFound from '@/Pages/Errors/NotFound.vue';
import { useAuthStore } from '@/stores/auth';
import { useHttpErrorStore } from '@/stores/httpError';
import { useUserStore } from '@/stores/user';
import { watch } from 'vue';

const authStore = useAuthStore();
const userStore = useUserStore();
const httpErrorStore = useHttpErrorStore();

// When auth token changes, fetch user data
watch(
    () => authStore.token,
    (newToken) => {
        if (newToken) {
            userStore.fetchUser(true);
        } else {
            userStore.invalidateUser();
        }
    },
);

// Initial user fetch if already authenticated
if (authStore.isAuthenticated()) {
    userStore.fetchUser();
}
</script>

<template>
    <Forbidden v-if="httpErrorStore.status === 403" />
    <NotFound v-else-if="httpErrorStore.status === 404" />
    <router-view v-else />
</template>
