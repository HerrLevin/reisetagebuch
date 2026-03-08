<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SideMenu from '@/Layouts/Partials/SideMenu.vue';
import ThemeSelector from '@/Layouts/Partials/ThemeSelector.vue';
import { useAppConfigurationStore } from '@/stores/appConfiguration';
import { useAuthStore } from '@/stores/auth';
import { useUserStore } from '@/stores/user';
import { Handshake, History, LogOut, Settings, User } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { RouterLink, useRouter } from 'vue-router';

const { t } = useI18n();

const appConfig = useAppConfigurationStore();
appConfig.fetchConfig();
const user = useUserStore();
const authStore = useAuthStore();
const router = useRouter();

if (!user.user) {
    user.fetchUser();
}

async function logout() {
    await authStore.logout();
    user.invalidateUser();
    router.push({ name: 'login', query: { loggedOut: 'true' } });
}
</script>

<template>
    <div class="navbar bg-base-100 rounded-box shadow-sm">
        <div class="navbar-start">
            <RouterLink to="/home">
                <ApplicationLogo
                    class="h-10 w-10 cursor-pointer md:inline-block"
                />
            </RouterLink>
        </div>
        <div class="navbar-center">
            <SideMenu class="hidden md:flex" />
            <slot class="flex md:hidden"></slot>
        </div>
        <div class="navbar-end">
            <div class="dropdown dropdown-end">
                <div
                    tabindex="0"
                    role="button"
                    class="btn btn-ghost btn-circle"
                >
                    <div class="avatar">
                        <div class="bg-primary size-10 rounded-full">
                            <img
                                v-if="user.user?.avatar"
                                :src="`/files/` + user.user?.avatar"
                                :alt="user.user?.name"
                            />
                        </div>
                    </div>
                </div>
                <ul
                    tabindex="0"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow"
                >
                    <li>
                        <ThemeSelector />
                    </li>
                    <template v-if="user.user">
                        <li v-if="user.user">
                            <RouterLink :to="`/profile/${user.user?.username}`">
                                <User class="size-5" />
                                {{ t('profile.title') }}
                            </RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/account">
                                <Settings class="size-4" />
                                {{ t('settings.title') }}
                            </RouterLink>
                        </li>
                        <li v-if="user.user?.canInviteUsers">
                            <RouterLink to="/invites">
                                <Handshake class="size-4" />
                                {{ t('app.invite_users') }}
                            </RouterLink>
                        </li>
                        <li>
                            <RouterLink to="/location-history">
                                <History class="size-4" />
                                {{ t('app.location_history') }}
                            </RouterLink>
                        </li>
                        <li>
                            <button @click="logout()">
                                <LogOut class="size-4" /> {{ t('app.logout') }}
                            </button>
                        </li>
                    </template>
                    <template v-else>
                        <li>
                            <RouterLink to="/login">
                                {{ t('auth.login.title') }}
                            </RouterLink>
                        </li>
                        <li v-if="appConfig.canRegister()">
                            <RouterLink to="/register">
                                {{ t('auth.register.title') }}
                            </RouterLink>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</template>
