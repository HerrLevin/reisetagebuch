import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';

import ConfirmPassword from '@/Pages/Auth/ConfirmPassword.vue';
import ForgotPassword from '@/Pages/Auth/ForgotPassword.vue';
import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
import ResetPassword from '@/Pages/Auth/ResetPassword.vue';
import VerifyEmail from '@/Pages/Auth/VerifyEmail.vue';
import EditPost from '@/Pages/EditPost.vue';
import NotFound from '@/Pages/Errors/NotFound.vue';
import Home from '@/Pages/Home.vue';
import Invites from '@/Pages/Invites.vue';
import LocationHistoryIndex from '@/Pages/LocationHistory/Index.vue';
import CreateLocationPost from '@/Pages/NewPostDialog/CreateLocationPost.vue';
import CreateTextPost from '@/Pages/NewPostDialog/CreateTextPost.vue';
import CreateTransportPost from '@/Pages/NewPostDialog/CreateTransportPost.vue';
import EditManualGPSTrack from '@/Pages/NewPostDialog/EditManualGPSTrack.vue';
import EditTransportTimes from '@/Pages/NewPostDialog/EditTransportTimes.vue';
import ListDepartures from '@/Pages/NewPostDialog/ListDepartures.vue';
import ListLocations from '@/Pages/NewPostDialog/ListLocations.vue';
import ListStopovers from '@/Pages/NewPostDialog/ListStopovers.vue';
import NotificationsIndex from '@/Pages/Notifications/Index.vue';
import PostsFilter from '@/Pages/Posts/Filter.vue';
import Likes from '@/Pages/Posts/Likes.vue';
import SinglePost from '@/Pages/Posts/SinglePost.vue';
import ProfileShow from '@/Pages/Profile/Show.vue';
import ProfileShowMap from '@/Pages/Profile/ShowMap.vue';
import EditSettings from '@/Pages/Settings/Edit.vue';
import CreateTrip from '@/Pages/Trips/Create.vue';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'welcome',
        meta: { guest: true },
        redirect: () => {
            return { path: '/login' };
        },
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true },
        props: true,
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guest: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
        meta: { guest: true },
    },
    {
        path: '/reset-password/:token',
        name: 'reset-password',
        component: ResetPassword,
        meta: { guest: true },
    },
    {
        path: '/verify-email',
        name: 'verify-email',
        component: VerifyEmail,
        meta: { auth: true },
    },
    {
        path: '/confirm-password',
        name: 'confirm-password',
        component: ConfirmPassword,
        meta: { auth: true },
    },
    {
        path: '/home',
        name: 'home',
        component: Home,
        meta: { auth: true },
    },
    {
        path: '/home/global',
        name: 'home.global',
        component: Home,
        meta: { auth: true },
    },
    {
        path: '/settings',
        name: 'account.edit',
        component: EditSettings,
        meta: { auth: true },
    },
    {
        path: '/location-history',
        name: 'location-history.index',
        component: LocationHistoryIndex,
        meta: { auth: true },
    },
    {
        path: '/posts/filter',
        name: 'posts.filter',
        component: PostsFilter,
        meta: { auth: true },
    },
    {
        path: '/posts/create',
        name: 'posts.create.post',
        component: CreateLocationPost,
        meta: { auth: true },
    },
    {
        path: '/posts/new',
        name: 'posts.create.text',
        component: CreateTextPost,
        meta: { auth: true },
    },
    {
        path: '/posts/:postId/edit',
        name: 'posts.edit',
        component: EditPost,
        meta: { auth: true },
        props: true,
    },
    {
        path: '/posts/transport/departures',
        name: 'posts.create.departures',
        component: ListDepartures,
        meta: { auth: true },
    },
    {
        path: '/posts/transport/stopovers',
        name: 'posts.create.stopovers',
        component: ListStopovers,
        meta: { auth: true },
    },
    {
        path: '/posts/transport/create',
        name: 'posts.create.transport-post',
        component: CreateTransportPost,
        meta: { auth: true },
    },
    {
        path: '/posts/transport/exit/edit',
        name: 'posts.edit.transport-post',
        component: ListStopovers,
        meta: { auth: true },
    },
    {
        path: '/posts/transport/:postId/times/edit',
        name: 'posts.edit.transport-times',
        component: EditTransportTimes,
        meta: { auth: true },
        props: true,
    },
    {
        path: '/posts/transport/:postId/track',
        name: 'posts.edit.transport-track',
        component: EditManualGPSTrack,
        meta: { auth: true },
        props: true,
    },
    {
        path: '/posts/location',
        name: 'posts.create.start',
        component: ListLocations,
        meta: { auth: true },
    },
    {
        path: '/posts/:postId',
        name: 'posts.show',
        component: SinglePost,
        props: true,
    },
    {
        path: '/posts/:postId/likes',
        name: 'posts.likes',
        component: Likes,
        props: true,
    },
    {
        path: '/trips/create',
        name: 'trips.create',
        component: CreateTrip,
        meta: { auth: true },
    },
    {
        path: '/invites',
        name: 'invites.index',
        component: Invites,
        meta: { auth: true },
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: NotificationsIndex,
        meta: { auth: true },
    },
    {
        path: '/profile/:username',
        name: 'profile.show',
        component: ProfileShow,
        props: true,
    },
    {
        path: '/profile/:username/map',
        name: 'profile.map',
        component: ProfileShowMap,
        props: true,
    },
    {
        path: '/socialite/traewelling/callback',
        name: 'socialite.traewelling.callback',
        component: EditSettings,
    },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
