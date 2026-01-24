<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SideMenu from '@/Layouts/Partials/SideMenu.vue';
import ThemeSelector from '@/Layouts/Partials/ThemeSelector.vue';
import { useAppConfigurationStore } from '@/stores/appConfiguration';
import { useUserStore } from '@/stores/user';
import { Link } from '@inertiajs/vue3';
import { Handshake, History, LogOut, Settings, User } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const appConfig = useAppConfigurationStore();
appConfig.fetchConfig();
const user = useUserStore();
if (!user.user) {
    user.fetchUser();
}
</script>

<template>
    <div class="navbar bg-base-100 rounded-box shadow-sm">
        <div class="navbar-start">
            <Link as="div" :href="route('dashboard')">
                <ApplicationLogo
                    class="h-10 w-10 cursor-pointer md:inline-block"
                />
            </Link>
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
                            <Link
                                :href="
                                    route('profile.show', user.user?.username)
                                "
                            >
                                <User class="size-5" />
                                {{ t('profile.title') }}
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('account.edit')">
                                <Settings class="size-4" />
                                {{ t('settings.title') }}
                            </Link>
                        </li>
                        <li v-if="user.user?.canInviteUsers">
                            <Link :href="route('invites.index')">
                                <Handshake class="size-4" />
                                {{ t('app.invite_users') }}
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('location-history.index')">
                                <History class="size-4" />
                                {{ t('app.location_history') }}
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                @click="user.invalidateUser()"
                            >
                                <LogOut class="size-4" /> {{ t('app.logout') }}
                            </Link>
                        </li>
                    </template>
                    <template v-else>
                        <li>
                            <Link :href="route('login')">
                                {{ t('auth.login.title') }}
                            </Link>
                        </li>
                        <li v-if="appConfig.canRegister()">
                            <Link :href="route('register')">
                                {{ t('auth.register.title') }}
                            </Link>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</template>
