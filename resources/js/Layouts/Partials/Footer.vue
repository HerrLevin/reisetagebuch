<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LocaleChanger from '@/Components/LocaleChanger.vue';
import { useAppConfigurationStore } from '@/stores/appConfiguration';

const config = useAppConfigurationStore();
config.fetchConfig();
</script>

<template>
    <footer
        class="footer sm:footer-horizontal bg-base-200 text-base-content p-10 pb-30 md:pb-10"
    >
        <aside>
            <ApplicationLogo class="h-12 w-12 cursor-pointer md:inline-block" />
            <p>
                {{ config.appName() }}
                <br />
                <a
                    v-if="config.appVersion() !== '0.0.0'"
                    class="link"
                    :href="`https://github.com/HerrLevin/reisetagebuch/releases/tag/${config.appVersion()}`"
                    target="_blank"
                >
                    {{ config.appVersion() }}
                </a>
                <a
                    v-else
                    class="link"
                    href="https://github.com/HerrLevin/reisetagebuch"
                    target="_blank"
                >
                    {{ $t('footer.version_unknown') }}
                </a>
            </p>
        </aside>
        <nav></nav>
        <nav></nav>
        <nav>
            <h6 class="footer-title">{{ $t('footer.legal.title') }}</h6>
            <a class="link link-hover">{{
                $t('footer.legal.privacy_policy')
            }}</a>
            <a class="link link-hover">{{ $t('footer.legal.imprint') }}</a>
        </nav>
        <nav>
            <LocaleChanger />
        </nav>
    </footer>
</template>
