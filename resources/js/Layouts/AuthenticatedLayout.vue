<script setup lang="ts">
import Dock from '@/Layouts/Partials/Dock.vue';
import Footer from '@/Layouts/Partials/Footer.vue';
import LocationHistoryChecker from '@/Layouts/Partials/LocationHistoryChecker.vue';
import NavBar from '@/Layouts/Partials/NavBar.vue';
import SideNav from '@/Layouts/Partials/SideNav.vue';
</script>

<template>
    <div>
        <div
            class="container mx-auto mb-2 max-w-screen-xl grow px-0 pt-0 pb-10 sm:px-5 md:pt-2"
        >
            <NavBar>
                <div class="md:hidden">
                    <slot name="header" class="md-hidden" />
                </div>
            </NavBar>

            <div
                class="mx-auto mt-4 grid w-full grid-cols-4 gap-4 md:mt-12 md:grid-cols-5 md:px-10"
            >
                <div class="col-span-1 hidden md:block">
                    <div class="card bg-base-100 min-w-full p-0 shadow-md">
                        <SideNav />
                    </div>
                </div>
                <div class="col-span-4">
                    <main class="mb-12 w-full pb-12 md:mb-0 md:pb-0">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
        <Footer />
        <Dock class="flex md:hidden" />
        <LocationHistoryChecker />
    </div>
</template>
