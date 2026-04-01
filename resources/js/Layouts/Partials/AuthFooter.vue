<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { useAppConfigurationStore } from '@/stores/appConfiguration';

const config = useAppConfigurationStore();
config.fetchConfig();
</script>

<template>
    <footer
        class="footer sm:footer-horizontal bg-base-200 text-base-content p-10"
    >
        <aside>
            <ApplicationLogo class="h-12 w-12 cursor-pointer md:inline-block" />
            <p>
                {{ config.configuration?.appName }}
                <br />
                <a
                    :href="`https://github.com/HerrLevin/reisetagebuch/releases/tag/${config.configuration?.reisetagebuchVersion}`"
                    target="_blank"
                    >{{ config.configuration?.reisetagebuchVersion }}</a
                >
            </p>
        </aside>
        <nav>
            <h6 class="footer-title">Services</h6>
            <a class="link link-hover">Branding</a>
            <a class="link link-hover">Design</a>
            <a class="link link-hover">Marketing</a>
            <a class="link link-hover">Advertisement</a>
        </nav>
        <nav>
            <h6 class="footer-title">Company</h6>
            <a class="link link-hover">About us</a>
            <a class="link link-hover">Contact</a>
            <a class="link link-hover">Jobs</a>
            <a class="link link-hover">Press kit</a>
        </nav>
        <nav>
            <h6 class="footer-title">Legal</h6>
            <a class="link link-hover">Terms of use</a>
            <a class="link link-hover">Privacy policy</a>
            <a class="link link-hover">Cookie policy</a>
        </nav>
    </footer>
</template>

<style scoped></style>
