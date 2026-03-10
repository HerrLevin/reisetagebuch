<script setup lang="ts">
import { useUserStore } from '@/stores/user';
import {
    Filter,
    Handshake,
    History,
    LogOut,
    Route,
    Settings,
    User,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useAppConfigurationStore } from '@/stores/appConfiguration';
import ThemeSelector from '@/Layouts/Partials/ThemeSelector.vue';
const { t } = useI18n();
const user = useUserStore();
const authStore = useAuthStore();

const appConfig = useAppConfigurationStore();
appConfig.fetchConfig();
const router = useRouter();

defineProps({
    class: {
        type: String,
        default: 'menu w-full',
    },
});

const links = [
    {
        link: {
            name: 'profile.show',
            params: { username: user.user?.username },
        },
        label: 'profile.title',
        icon: User,
        condition: !!user.user,
    },
    {
        link: { name: 'posts.filter' },
        label: 'posts.filter.title',
        icon: Filter,
        condition: !!user.user,
    },
    {
        link: { name: 'trips.create' },
        label: 'new_route.title',
        icon: Route,
        condition: !!user.user,
    },
    {
        link: { name: 'invites.index' },
        label: 'app.invite_users',
        icon: Handshake,
        condition: !!user.user && user.user.canInviteUsers,
    },
    {
        link: { name: 'location-history.index' },
        label: 'app.location_history',
        icon: History,
        condition: !!user.user,
    },
    {
        link: { name: 'account.edit' },
        label: 'settings.title',
        icon: Settings,
        condition: !!user.user,
    },
    {
        link: { name: 'login' },
        label: 'auth.login.title',
        icon: null,
        condition: !user.user,
    },
    {
        link: { name: 'register' },
        label: 'auth.register.title',
        icon: null,
        condition: !user.user && appConfig.canRegister(),
    },
];

const filteredLinks = computed(() => {
    return links.filter((link) => {
        return link.condition;
    });
});

async function logout() {
    await authStore.logout();
    user.invalidateUser();
    router.push({ name: 'login', query: { loggedOut: 'true' } });
}
</script>

<template>
    <!-- eslint-disable-next-line vue/no-parsing-error -->
    <ul :class="class">
        <li>
            <ThemeSelector />
        </li>
        <li v-for="link in filteredLinks" :key="link.label">
            <RouterLink :to="link.link" active-class="menu-active">
                <component
                    :is="link.icon"
                    v-if="link.icon"
                    class="size-5"
                ></component>
                {{ t(link.label) }}
            </RouterLink>
        </li>
        <li v-if="user.user">
            <button @click="logout()">
                <LogOut class="size-4" /> {{ t('app.logout') }}
            </button>
        </li>
    </ul>
</template>
