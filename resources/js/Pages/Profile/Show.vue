<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AvatarMenu from '@/Pages/Profile/Partials/AvatarMenu.vue';
import Banner from '@/Pages/Profile/Partials/Banner.vue';
import BioText from '@/Pages/Profile/Partials/BioText.vue';
import BottomInfo from '@/Pages/Profile/Partials/BottomInfo.vue';
import ProfileMenu from '@/Pages/Profile/Partials/ProfileMenu.vue';
import Statistics from '@/Pages/Profile/Partials/Statistics.vue';
import { Head } from '@inertiajs/vue3';

const textTest = `Hi!

Wo?

Lorem ipsum dolor sit amet, consectetur adipiscing elit.
`;
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">Profile</h2>
        </template>
        <Banner />

        <div class="mb-4 space-y-1 px-5 md:px-0">
            <AvatarMenu />
            <div class="-mt-3">
                <div class="flex items-center">
                    <h3 class="truncate text-2xl font-bold">Max Mustermann</h3>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm">@mmustermann</span>
                </div>
            </div>
            <Statistics />
            <BioText :text="textTest" />
            <BottomInfo />
            <ProfileMenu />
        </div>

        <div class="card bg-base-100 min-w-full shadow-md">
            Hier kommen dann die posts hin!
        </div>
    </AuthenticatedLayout>
</template>
