import router from '@/router';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import { Api } from '../types/Api.gen';

const apiBaseUrl = '/api';

const api = new Api({ baseURL: apiBaseUrl });

export function setupApiAuth(token: string | null) {
    if (token) {
        api.instance.defaults.headers.common['Authorization'] =
            `Bearer ${token}`;
    } else {
        delete api.instance.defaults.headers.common['Authorization'];
    }
}

// middleware to clear the token if the api returns a 401
api.instance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const auth = useAuthStore();

            if (!auth.isAuthenticated()) {
                return;
            }

            auth.setToken(null);
            setupApiAuth(null);
            useUserStore().invalidateUser();
            router
                .push({
                    name: 'login',
                    query: {
                        loggedOut: 'true',
                    },
                })
                .then((r) => r);
        }

        if (error.response?.status === 403) {
            router
                .push({
                    name: 'forbidden',
                })
                .then((r) => r);
        }

        if (error.response?.status === 404) {
            router
                .push({
                    name: 'not-found',
                })
                .then((r) => r);
        }
        return Promise.reject(error);
    },
);

export { api };
