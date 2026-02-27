import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'welcome',
        component: () => import('@/Pages/Welcome.vue'),
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/Pages/Auth/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/Pages/Auth/Register.vue'),
        meta: { guest: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('@/Pages/Auth/ForgotPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/reset-password/:token',
        name: 'reset-password',
        component: () => import('@/Pages/Auth/ResetPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/verify-email',
        name: 'verify-email',
        component: () => import('@/Pages/Auth/VerifyEmail.vue'),
        meta: { auth: true },
    },
    {
        path: '/confirm-password',
        name: 'confirm-password',
        component: () => import('@/Pages/Auth/ConfirmPassword.vue'),
        meta: { auth: true },
    },
    {
        path: '/home',
        name: 'dashboard',
        component: () => import('@/Pages/Dashboard.vue'),
        meta: { auth: true },
    },
    {
        path: '/account',
        name: 'account.edit',
        component: () => import('@/Pages/Settings/Edit.vue'),
        meta: { auth: true },
    },
    {
        path: '/location-history',
        name: 'location-history.index',
        component: () => import('@/Pages/LocationHistory/Index.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/filter',
        name: 'posts.filter',
        component: () => import('@/Pages/Posts/Filter.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/create',
        name: 'posts.create.post',
        component: () => import('@/Pages/NewPostDialog/CreateLocationPost.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/new',
        name: 'posts.create.text',
        component: () => import('@/Pages/NewPostDialog/CreateTextPost.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/:postId/edit',
        name: 'posts.edit',
        component: () => import('@/Pages/EditPost.vue'),
        meta: { auth: true },
        props: true,
    },
    {
        path: '/posts/transport/departures',
        name: 'posts.create.departures',
        component: () => import('@/Pages/NewPostDialog/ListDepartures.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/transport/stopovers',
        name: 'posts.create.stopovers',
        component: () => import('@/Pages/NewPostDialog/ListStopovers.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/transport/create',
        name: 'posts.create.transport-post',
        component: () =>
            import('@/Pages/NewPostDialog/CreateTransportPost.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/transport/exit/edit',
        name: 'posts.edit.transport-post',
        component: () => import('@/Pages/NewPostDialog/ListStopovers.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/transport/:postId/times/edit',
        name: 'posts.edit.transport-times',
        component: () => import('@/Pages/NewPostDialog/EditTransportTimes.vue'),
        meta: { auth: true },
        props: true,
    },
    {
        path: '/posts/location',
        name: 'posts.create.start',
        component: () => import('@/Pages/NewPostDialog/ListLocations.vue'),
        meta: { auth: true },
    },
    {
        path: '/posts/:postId',
        name: 'posts.show',
        component: () => import('@/Pages/SinglePost.vue'),
        props: true,
    },
    {
        path: '/trips/create',
        name: 'trips.create',
        component: () => import('@/Pages/Trips/Create.vue'),
        meta: { auth: true },
    },
    {
        path: '/invites',
        name: 'invites.index',
        component: () => import('@/Pages/Invites.vue'),
        meta: { auth: true },
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: () => import('@/Pages/Notifications/Index.vue'),
        meta: { auth: true },
    },
    {
        path: '/profile/:username',
        name: 'profile.show',
        component: () => import('@/Pages/Profile/Show.vue'),
        props: true,
    },
    {
        path: '/profile/:username/map',
        name: 'profile.map',
        component: () => import('@/Pages/Profile/ShowMap.vue'),
        props: true,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
