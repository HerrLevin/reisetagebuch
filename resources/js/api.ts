import router from '@/router';
import { getServerUrl } from '@/Services/ServerConfig';
import { useAuthStore } from '@/stores/auth';
import { useHttpErrorStore } from '@/stores/httpError';
import { useLocationHistoryStore } from '@/stores/locationHistory';
import { useUserStore } from '@/stores/user';
import { Api } from '../types/Api.gen';

const api = new Api({ baseURL: '/api' });

// Resolves the configured instance host (native only; empty/same-origin on
// web) and points the API client at it. Must be awaited before any API call
// is made, so app.ts runs it before mounting.
export async function initApi(): Promise<void> {
    const serverUrl = await getServerUrl();
    api.instance.defaults.baseURL = `${serverUrl}/api`;
}

export function setupApiAuth(token: string | null) {
    if (token) {
        api.instance.defaults.headers.common['Authorization'] =
            `Bearer ${token}`;
    } else {
        delete api.instance.defaults.headers.common['Authorization'];
    }
}

api.instance.interceptors.request.use((config) => {
    const token = useAuthStore().token;
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    // Sent as a header rather than relying on the rtb_allow_history /
    // rtb_disallow_history cookies the backend also accepts: cookies set via
    // document.cookie in the WebView aren't visible to CapacitorHttp's
    // native request layer, so the preference silently failed to reach the
    // server on native builds. The backend treats the header identically.
    const allowHistory = useLocationHistoryStore().allowHistory;
    if (allowHistory === true) {
        config.headers['X-RTB-ALLOW-HISTORY'] = 'true';
    } else if (allowHistory === false) {
        config.headers['X-RTB-DISALLOW-HISTORY'] = 'true';
    }

    return config;
});

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
            if (error.response?.data?.code === 'privacyPolicyRequired') {
                if (router.currentRoute.value.name !== 'privacy-policy') {
                    router
                        .push({
                            name: 'privacy-policy',
                            query: {
                                redirect: router.currentRoute.value.fullPath,
                            },
                        })
                        .then((r) => r);
                }
            } else {
                useHttpErrorStore().setError(403);
            }
        }

        if (
            error.response?.status === 404 &&
            !error.config?.url?.includes('/app/privacy-policy')
        ) {
            useHttpErrorStore().setError(404);
        }
        return Promise.reject(error);
    },
);

export { api };
