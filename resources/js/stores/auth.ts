import { api, setupApiAuth } from '@/api';
import { isNative } from '@/Services/ServerConfig';
import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore(
    'auth',
    () => {
        // Native app only (and Cypress); the web SPA authenticates via the
        // session + Passport's laravel_token cookie.
        const token = ref<string | null>(null);
        const expiresAt = ref<string | null>(null);
        // Not persisted: re-read from the server-rendered page on every load.
        const webSession = ref(
            document.querySelector<HTMLMetaElement>(
                'meta[name="rtb-authenticated"]',
            )?.content === '1',
        );

        function setToken(
            newToken: string | null,
            newExpiresAt: string | null = null,
        ) {
            token.value = newToken;
            expiresAt.value = newExpiresAt;
            setupApiAuth(newToken);
            if (!newToken) {
                webSession.value = false;
            }
        }

        function initializeAuth() {
            if (token.value) {
                setupApiAuth(token.value);
            }
        }

        async function logout() {
            try {
                if (isNative()) {
                    await api.auth.logout();
                } else {
                    // Ends the session and clears the laravel_token cookie.
                    await axios.post('/logout');
                }
            } catch {
                // ignore errors on logout
            }
            setToken(null);
        }

        const isAuthenticated = () => !!token.value || webSession.value;

        return {
            token,
            expiresAt,
            setToken,
            initializeAuth,
            logout,
            isAuthenticated,
        };
    },
    {
        persist: {
            pick: ['token', 'expiresAt'],
        },
    },
);
