<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import { watch } from 'vue';

const authStore = useAuthStore();
const userStore = useUserStore();

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
    <router-view />
</template>
