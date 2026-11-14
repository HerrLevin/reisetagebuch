import Dashboard from '@admin/Pages/Dashboard.vue';
import Imprint from '@admin/Pages/Imprint.vue';
import PrivacyPolicy from '@admin/Pages/PrivacyPolicy.vue';
import { createRouter, createWebHistory } from 'vue-router';

export default createRouter({
    history: createWebHistory('/admin/'),
    routes: [
        { path: '/', name: 'dashboard', component: Dashboard },
        { path: '/imprint', name: 'imprint', component: Imprint },
        {
            path: '/privacy-policy',
            name: 'privacy-policy',
            component: PrivacyPolicy,
        },
        { path: '/:pathMatch(.*)*', redirect: { name: 'dashboard' } },
    ],
});
