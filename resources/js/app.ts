import '../css/app.css';
import './bootstrap';

import App from '@/App.vue';
import i18n from '@/i18n';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';
import { createPinia } from 'pinia';
import { createPersistedState } from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';

const pinia = createPinia();
pinia.use(createPersistedState());

const app = createApp(App);

app.use(pinia);
app.use(i18n);

// Initialize auth (restore token from persisted state to axios headers)
const authStore = useAuthStore();
authStore.initializeAuth();

// Set up navigation guard after pinia is available
router.beforeEach((to, _from, next) => {
    if (to.meta.auth && !authStore.isAuthenticated()) {
        next({ name: 'login' });
    } else if (to.meta.guest && authStore.isAuthenticated()) {
        next({ name: 'home' });
    } else {
        next();
    }
});

app.use(router);

app.mount('#app');
