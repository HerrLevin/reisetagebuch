<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AvatarMenu from '@/Pages/Profile/Partials/AvatarMenu.vue';
import Banner from '@/Pages/Profile/Partials/Banner.vue';
import BioText from '@/Pages/Profile/Partials/BioText.vue';
import BottomInfo from '@/Pages/Profile/Partials/BottomInfo.vue';
import ProfileMenu from '@/Pages/Profile/Partials/ProfileMenu.vue';
import Statistics from '@/Pages/Profile/Partials/Statistics.vue';
import type { UserDto } from '@/types';
import { Head } from '@inertiajs/vue3';
import type { PropType } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold">
                {{ t('profile.profile_of', { name: user.name }) }}
            </h2>
        </template>
        <Banner :src="user.header || ''" />

        <div class="mb-4 space-y-1 px-5 md:px-0">
            <AvatarMenu :user="user" />
            <div class="-mt-1">
                <div class="flex items-center">
                    <h3 class="truncate text-2xl font-bold">{{ user.name }}</h3>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm">@{{ user.username }}</span>
                </div>
            </div>
            <Statistics :user="user" />
            <BioText :user="user" />
            <BottomInfo :user="user" />
            <ProfileMenu :user="user" />
        </div>

        <div class="card bg-base-100 min-w-full shadow-md">
            <slot></slot>
        </div>
    </AuthenticatedLayout>
</template>
