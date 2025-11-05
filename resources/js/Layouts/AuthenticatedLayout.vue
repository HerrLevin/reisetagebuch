<script setup lang="ts">
import FlashMessages from '@/Components/FlashMessages.vue';
import Dock from '@/Layouts/Partials/Dock.vue';
import LocationHistoryChecker from '@/Layouts/Partials/LocationHistoryChecker.vue';
import NavBar from '@/Layouts/Partials/NavBar.vue';
</script>

<template>
    <div>
        <div
            class="container mx-auto mb-2 max-w-screen-xl grow px-0 pt-0 sm:px-5 md:pt-2"
        >
            <NavBar>
                <div class="md:hidden">
                    <slot name="header" class="md-hidden" />
                </div>
            </NavBar>

            <div class="mt-4 md:mt-12">
                <div class="mx-auto max-w-4xl">
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
