<script setup lang="ts">
import { UserDto } from '@/types';
import { Link } from '@inertiajs/vue3';
import { PropType } from 'vue';

defineProps({
    user: {
        type: Object as PropType<UserDto>,
        required: true,
    },
});
</script>

<template>
    <ul class="menu menu-horizontal bg-base-200 rounded-box">
        <li>
            <Link :href="route('profile.show', user.username)"> posts </Link>
        </li>
        <li>
            <Link :href="route('profile.map', user.username)"> map </Link>
        </li>
    </ul>
</template>
