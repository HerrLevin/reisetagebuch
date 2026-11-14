<script setup lang="ts">
import { api } from '@admin/api';
import ThemeSelect from '@admin/Components/ThemeSelect.vue';
import ApplicationLogo from '@shared/Components/ApplicationLogo.vue';
import axios from 'axios';
import {
    ArrowLeft,
    FileText,
    LayoutDashboard,
    Layers,
    LogOut,
    Menu,
    Scale,
    Telescope,
} from 'lucide-vue-next';
import { onMounted, ref, watch, type Component } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink, useRoute } from 'vue-router';
import { Feature } from '../../types/Api.gen';

const { t } = useI18n();
const route = useRoute();

// Mobile drawer state; the sidebar is always open from lg upwards.
const open = ref(false);
watch(
    () => route.fullPath,
    () => (open.value = false),
);

const links = [
    {
        to: { name: 'dashboard' },
        label: 'admin.dashboard',
        icon: LayoutDashboard,
    },
    {
        to: { name: 'imprint' },
        label: 'settings.imprint.title',
        icon: FileText,
    },
    {
        to: { name: 'privacy-policy' },
        label: 'settings.privacyPolicy.title',
        icon: Scale,
    },
];

const tools = ref<{ href: string; label: string; icon: Component }[]>([]);
onMounted(() => {
    api.app.getAppConfiguration().then(({ data }) => {
        const enabled = (feature: Feature) =>
            data.featureFlags.some((f) => f.name === feature && f.enabled);
        tools.value = [
            {
                href: '/horizon',
                label: 'Horizon',
                icon: Layers,
                feature: Feature.Horizon,
            },
            {
                href: '/telescope',
                label: 'Telescope',
                icon: Telescope,
                feature: Feature.Telescope,
            },
        ].filter((tool) => enabled(tool.feature));
    });
});

async function logout() {
    try {
        // Ends the session and clears the laravel_token cookie.
        await axios.post('/logout');
    } finally {
        window.location.href = '/login';
    }
}
</script>

<template>
    <div class="drawer lg:drawer-open min-h-screen">
        <input
            id="admin-drawer"
            v-model="open"
            type="checkbox"
            class="drawer-toggle"
        />

        <div class="drawer-content">
            <label
                for="admin-drawer"
                class="btn btn-square btn-ghost fixed top-3 left-3 z-10 lg:hidden"
                :aria-label="t('admin.open_menu')"
            >
                <Menu class="size-5" />
            </label>
            <main class="mx-auto max-w-6xl p-4 pt-16 lg:p-8">
                <slot />
            </main>
        </div>

        <div class="drawer-side z-20">
            <label
                for="admin-drawer"
                class="drawer-overlay"
                :aria-label="t('app.close_sidebar')"
            ></label>
            <aside class="bg-base-100 flex min-h-full w-64 flex-col p-4">
                <RouterLink
                    :to="{ name: 'dashboard' }"
                    class="mb-4 flex items-center gap-3 px-2"
                >
                    <ApplicationLogo class="size-10" />
                    <span class="font-semibold">{{ t('admin.title') }}</span>
                </RouterLink>

                <ul class="menu w-full grow p-0">
                    <li v-for="link in links" :key="link.label">
                        <RouterLink
                            :to="link.to"
                            active-class=""
                            exact-active-class="menu-active"
                        >
                            <component :is="link.icon" class="size-5" />
                            {{ t(link.label) }}
                        </RouterLink>
                    </li>
                </ul>

                <ul v-if="tools.length" class="menu w-full p-0">
                    <li class="menu-title">{{ t('admin.tools') }}</li>
                    <li v-for="tool in tools" :key="tool.label">
                        <a :href="tool.href">
                            <component :is="tool.icon" class="size-5" />
                            {{ tool.label }}
                        </a>
                    </li>
                </ul>

                <ul class="menu w-full p-0">
                    <li>
                        <a href="/home">
                            <ArrowLeft class="size-5" />
                            {{ t('admin.back_to_app') }}
                        </a>
                    </li>
                    <li>
                        <button type="button" @click="logout()">
                            <LogOut class="size-5" /> {{ t('app.logout') }}
                        </button>
                    </li>
                </ul>

                <ThemeSelect class="mt-4" />
            </aside>
        </div>
    </div>
</template>
