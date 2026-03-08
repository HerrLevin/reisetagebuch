import { api, setupApiAuth } from '@/api';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore(
    'auth',
    () => {
        const token = ref<string | null>(null);
        const expiresAt = ref<string | null>(null);

        function setToken(
            newToken: string | null,
            newExpiresAt: string | null = null,
        ) {
            token.value = newToken;
            expiresAt.value = newExpiresAt;
            setupApiAuth(newToken);
        }

        function initializeAuth() {
            if (token.value) {
                setupApiAuth(token.value);
            }
        }

        async function login(email: string, password: string) {
            const response = await api.auth.login({
                email,
                password,
            });
            setToken(response.data.token, response.data.expiresAt);
            return response.data;
        }

        async function register(data: {
            name: string;
            username: string;
            email: string;
            password: string;
            password_confirmation: string;
            invite?: string;
        }) {
            const response = await api.auth.register(data);
            setToken(response.data.token, response.data.expiresAt);
            return response.data;
        }

        async function logout() {
            try {
                await api.auth.logout();
            } catch {
                // ignore errors on logout
            }
            setToken(null);
        }

        const isAuthenticated = () => !!token.value;

        return {
            token,
            expiresAt,
            setToken,
            initializeAuth,
            login,
            register,
            logout,
            isAuthenticated,
        };
    },
    {
        persist: true,
    },
);
