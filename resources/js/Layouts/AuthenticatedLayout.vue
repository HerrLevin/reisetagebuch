<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import Dock from '@/Layouts/Partials/Dock.vue';
import LocationHistoryChecker from '@/Layouts/Partials/LocationHistoryChecker.vue';
import NavBar from '@/Layouts/Partials/NavBar.vue';
import SideMenu from '@/Layouts/Partials/SideMenu.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div>
        <div
            class="container mx-auto mb-2 max-w-screen-xl grow px-0 pt-0 sm:px-5 md:pt-2"
        >
            <NavBar>
                <Link as="div" :href="route('dashboard')">
                    <ApplicationLogo
                        class="hidden h-10 w-10 cursor-pointer md:inline-block"
                    />
                </Link>
                <a
                    v-if="!$slots.header"
                    class="btn btn-ghost text-xl md:hidden"
                >
                    <span>Reisetagebuch</span>
                </a>
                <div class="md:hidden">
                    <slot name="header" class="md-hidden" />
                </div>
            </NavBar>

            <div class="grid grid-cols-12 gap-4 md:mt-12">
                <div class="col-span-12 lg:col-span-3 lg:col-start-2">
                    <div class="flex justify-center"></div>
                    <SideMenu class="hidden md:flex" />
                </div>
                <div
                    class="col-span-12 md:flex md:justify-center lg:col-span-7"
                >
                    <main class="mb-12 w-full pb-12 md:mb-0 md:pb-0">
                        <FlashMessages />
                        <slot />
                    </main>
                </div>
            </div>
        </div>
        <Dock class="flex md:hidden" />
        <LocationHistoryChecker />
    </div>
</template>
