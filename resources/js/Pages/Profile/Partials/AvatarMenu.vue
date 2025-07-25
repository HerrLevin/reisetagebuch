<script setup lang="ts">
import EllipsisVertical from '@/Icons/EllipsisVertical.vue';
import ProfileEditModal from '@/Pages/Profile/Partials/ProfileEditModal.vue';
import type { UserDto } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { PropType } from 'vue';

const authUser = usePage().props.auth.user ?? null;
defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});
</script>

<template>
    <div class="flex items-start justify-between">
        <div class="avatar relative -mt-10 ml-0 sm:-mt-16 sm:ml-8">
            <div
                class="border-base-200 bg-primary w-20 rounded-xl border-5 sm:w-32"
            >
                <img
                    v-if="user.avatar?.length"
                    :src="user.avatar"
                    alt="Avatar"
                />
            </div>
        </div>
        <div class="flex items-center gap-2">
            <ProfileEditModal v-if="authUser" :user="user" />
            <button class="btn btn-circle" type="button">
                <EllipsisVertical />
            </button>
        </div>
    </div>
</template>
