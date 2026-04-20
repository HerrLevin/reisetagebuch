<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LocaleChanger from '@/Components/LocaleChanger.vue';
import { useAppConfigurationStore } from '@/stores/appConfiguration';
import { computed } from 'vue';

const config = useAppConfigurationStore();
config.fetchConfig();

const appVersion = computed(() => {
    const version = config.appVersion();
    return version && version !== '0.0.0' ? version : null;
});

const versionLink = computed(() => {
    if (appVersion.value?.includes('.')) {
        const version = appVersion.value.startsWith('v')
            ? appVersion.value
            : `v${appVersion.value}`;
        return `https://github.com/HerrLevin/reisetagebuch/releases/tag/${version}`;
    }

    return `https://github.com/HerrLevin/reisetagebuch/commit/${appVersion.value}`;
});
</script>

<template>
    <footer
        class="footer sm:footer-horizontal bg-base-200 text-base-content p-10 pb-30 md:pb-10"
    >
        <aside>
            <ApplicationLogo class="h-12 w-12 cursor-pointer md:inline-block" />
            <p>
                <a
                    href="https://github.com/HerrLevin/reisetagebuch"
                    class="link"
                    target="_blank"
                >
                    {{ config.appName() }}
                </a>
                <br />
                <a
                    v-if="appVersion"
                    class="link"
                    :href="versionLink"
                    target="_blank"
                >
                    {{ config.appVersion() }}
                </a>
                <span v-else class="italic">
                    {{ $t('footer.version_unknown') }}
                </span>
            </p>
        </aside>
        <nav></nav>
        <nav></nav>
        <nav>
            <h6 class="footer-title">{{ $t('footer.legal.title') }}</h6>
            <a class="link link-hover">{{
                $t('footer.legal.privacy_policy')
            }}</a>
            <router-link :to="{ name: 'imprint' }" class="link link-hover">
                {{ $t('footer.legal.imprint') }}
            </router-link>
        </nav>
        <nav>
            <LocaleChanger />
        </nav>
    </footer>
</template>
