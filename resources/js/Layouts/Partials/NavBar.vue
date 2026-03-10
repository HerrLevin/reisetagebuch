<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SideMenu from '@/Layouts/Partials/NavMenu.vue';
import SideNav from '@/Layouts/Partials/SideNav.vue';
import { useUserStore } from '@/stores/user';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';

const { t } = useI18n();

const user = useUserStore();

if (!user.user) {
    user.fetchUser();
}
</script>

<template>
    <div class="drawer drawer-end">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content">
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
                    <RouterLink
                        v-if="user.user"
                        :to="{
                            name: 'profile.show',
                            params: { username: user.user.username },
                        }"
                        class="avatar hidden md:block"
                    >
                        <span class="sr-only">{{ t('app.open_sidebar') }}</span>
                        <div class="bg-primary size-10 rounded-full">
                            <img
                                v-if="user.user?.avatar"
                                :src="`/files/` + user.user?.avatar"
                                :alt="user.user?.name"
                            />
                        </div>
                    </RouterLink>
                    <label
                        for="sidebar-drawer"
                        class="drawer-button avatar cursor-pointer md:hidden"
                    >
                        <span class="sr-only">{{ t('app.open_sidebar') }}</span>
                        <div class="bg-primary size-10 rounded-full">
                            <img
                                v-if="user.user?.avatar"
                                :src="`/files/` + user.user?.avatar"
                                :alt="user.user?.name"
                            />
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div class="drawer-side">
            <label
                for="sidebar-drawer"
                :aria-label="t('app.close_sidebar')"
                class="drawer-overlay"
            ></label>
            <side-nav class="bg-base-200 menu min-h-full w-80 p-4" />
        </div>
    </div>
</template>
