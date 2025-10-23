<script setup lang="ts">
import ThemeSelector from '@/Layouts/Partials/ThemeSelector.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Handshake, History, LogOut, Menu, Settings } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <div class="navbar bg-base-100 rounded-box shadow-sm">
        <div class="navbar-start">
            <div class="dropdown">
                <div
                    tabindex="0"
                    role="button"
                    class="btn btn-ghost btn-circle"
                >
                    <Menu class="size-5" />
                </div>
                <ul
                    tabindex="0"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow"
                >
                    <li>
                        <Link :href="route('account.edit')">
                            <Settings class="size-4" />
                            {{ t('settings.title') }}
                        </Link>
                    </li>
                    <li v-if="usePage()?.props?.canInvite">
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
                        <Link :href="route('logout')" method="post" as="button">
                            <LogOut class="size-4" /> {{ t('app.logout') }}
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
        <div class="navbar-center">
            <slot></slot>
        </div>
        <div class="navbar-end">
            <ThemeSelector />
        </div>
    </div>
</template>
