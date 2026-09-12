import '../css/app.css';
import './bootstrap';

import { initApi } from '@/api';
import App from '@/App.vue';
import i18n from '@/i18n';
import router from '@/router';
import { getServerUrl, isNative } from '@/Services/ServerConfig';
import { useAuthStore } from '@/stores/auth';
import { useHttpErrorStore } from '@/stores/httpError';
import { setWorkerUrl } from 'maplibre-gl';
import maplibreWorkerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';
import { createPinia } from 'pinia';
import { createPersistedState } from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';

setWorkerUrl(maplibreWorkerUrl);

// Must resolve before any API call is made (including ones triggered by the
// very first route's components), so the axios client targets the right
// instance host on native builds.
await initApi();

const pinia = createPinia();
pinia.use(createPersistedState());

const app = createApp(App);

app.use(pinia);
app.use(i18n);

// Initialize auth (restore token from persisted state to axios headers)
const authStore = useAuthStore();
authStore.initializeAuth();

// Set up navigation guard after pinia is available
const httpErrorStore = useHttpErrorStore();
router.beforeEach(async (to, _from, next) => {
    httpErrorStore.clearError();
    if (isNative() && to.name !== 'connect-server' && !(await getServerUrl())) {
        next({ name: 'connect-server' });
    } else if (to.meta.auth && !authStore.isAuthenticated()) {
        next({ name: 'login' });
    } else if (to.meta.guest && authStore.isAuthenticated()) {
        next({ name: 'home' });
    } else {
        next();
    }
});

app.use(router);

app.mount('#app');
