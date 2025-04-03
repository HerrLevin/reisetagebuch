<script setup lang="ts">
import { ref } from 'vue';
import SideMenu from '@/Layouts/Partials/SideMenu.vue';
import Dock from '@/Pages/Profile/Partials/Dock.vue';
import NavBar from '@/Layouts/Partials/NavBar.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div
            class="container mx-auto mb-2 max-w-screen-xl grow px-0 pt-0 sm:px-5 md:pt-8"
        >
            <NavBar />
            <div class="mt-12 grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-3 lg:col-start-2">
                    <SideMenu class="hidden md:flex" />
                </div>
                <div
                    class="col-span-12 md:flex md:justify-center lg:col-span-7"
                >
                    <main class="w-full">
                        <main class="md:flex md:justify-center">
                            <slot />
                        </main>
                    </main>
                </div>
            </div>
        </div>
        <Dock class="flex md:hidden" />
    </div>
</template>
