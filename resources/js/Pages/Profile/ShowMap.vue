<script setup lang="ts">
import LocationsMap from '@/Components/Maps/LocationsMap.vue';
import ProfileWrapper from '@/Pages/Profile/ProfileWrapper.vue';
import type { UserDto } from '@/types';
import { Head } from '@inertiajs/vue3';
import type { PropType } from 'vue';

defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Profile" />

    <ProfileWrapper :user="user">
        <div class="card bg-base-100 min-w-full shadow-md">
            <LocationsMap :user="user" />
        </div>
    </ProfileWrapper>
</template>
