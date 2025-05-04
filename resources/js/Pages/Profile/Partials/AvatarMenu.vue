<script setup lang="ts">
import EllipsisVertical from '@/Icons/EllipsisVertical.vue';
import type { UserDto } from '@/types';
import { PropType } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});

const authUser = usePage().props.auth.user;
</script>

<template>
    <div class="flex items-start justify-between">
        <div class="avatar relative -mt-10 ml-0 sm:-mt-16 sm:ml-8">
            <div class="border-base-200 w-20 rounded-xl border-5 sm:w-32">
                <img src="/assets/pexels-brenoanp-442535-1136575.jpg" />
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button
                class="btn rounded-full"
                if="user.id === authUser.id"
                type="button"
            >
                <div class="flex items-center">Edit Profile</div>
            </button>
            <button class="btn btn-circle" type="button">
                <EllipsisVertical />
            </button>
        </div>
    </div>
</template>
