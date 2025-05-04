<script setup lang="ts">
import NavBar from '@/Layouts/Partials/NavBar.vue';
import SideMenu from '@/Layouts/Partials/SideMenu.vue';
import Dock from '@/Pages/Profile/Partials/Dock.vue';
</script>

<template>
    <div>
        <div
            class="container mx-auto mb-2 max-w-screen-xl grow px-0 pt-0 sm:px-5 md:pt-2"
        >
            <NavBar class="md:hidden">
                <a v-if="!$slots.header" class="btn btn-ghost text-xl">
                    <span class="blur">Reisetagebuch</span>
                </a>
                <slot name="header" />
            </NavBar>
            <div class="grid grid-cols-12 gap-4 md:mt-12">
                <div class="col-span-12 lg:col-span-3 lg:col-start-2">
                    <SideMenu class="hidden md:flex" />
                </div>
                <div
                    class="col-span-12 md:flex md:justify-center lg:col-span-7"
                >
                    <main class="mb-12 w-full pb-12 md:mb-0 md:pb-0">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
        <Dock class="flex md:hidden" />
    </div>
</template>
