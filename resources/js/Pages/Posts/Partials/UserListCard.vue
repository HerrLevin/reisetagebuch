<script setup lang="ts">
import FollowButton from '@/Pages/Profile/Partials/FollowButton.vue';
import router from '@/router';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps<{
    user: UserDto;
}>();

function redirectToUser() {
    router.push({
        name: 'profile.show',
        params: { username: props.user.username },
    });
}
</script>

<template>
    <div class="avatar" @click="redirectToUser">
        <div class="bg-primary size-10 rounded-xl">
            <img
                v-if="user.avatar"
                :src="user.avatar"
                :alt="
                    t('posts.profile_picture_alt', {
                        name: user.name,
                    })
                "
            />
        </div>
    </div>
    <div class="list-col-grow ms-4" @click="redirectToUser">
        <RouterLink :to="`/profile/${user.username}`">
            <span class="font-bold hover:underline">
                {{ user.name }}
            </span>
            <span class="mx-1 opacity-60">·</span>
            <span class="text-xs opacity-60"> @{{ user.username }} </span>
        </RouterLink>
        <p class="opacity-60">
            {{ user.statistics.followersCount }}
            {{ t('profile.stats.followers', user.statistics.followersCount) }}
        </p>
    </div>
    <div>
        <FollowButton :user="user" />
    </div>
</template>
