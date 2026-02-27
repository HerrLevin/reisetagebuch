<script setup lang="ts">
import { UserDto } from '@/types';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { RouterLink } from 'vue-router';

const { t } = useI18n();

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
            <RouterLink :to="`/profile/${user.username}`">
                {{ t('profile.menu.posts') }}
            </RouterLink>
        </li>
        <li>
            <RouterLink :to="`/profile/${user.username}/map`">
                {{ t('profile.menu.map') }}
            </RouterLink>
        </li>
    </ul>
</template>
