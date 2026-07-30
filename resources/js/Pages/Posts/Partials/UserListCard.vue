<script setup lang="ts">
import FollowButton from '@/Pages/Profile/Partials/FollowButton.vue';
import router from '@/router';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps<{
    user: UserDto;
}>();

const isRemote = computed(() => !!props.user.profileUrl);

function redirectToUser() {
    if (isRemote.value) {
        window.open(props.user.profileUrl!, '_blank', 'noopener,noreferrer');

        return;
    }
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
        <a
            v-if="isRemote"
            :href="user.profileUrl!"
            target="_blank"
            rel="noopener noreferrer"
        >
            <span class="font-bold hover:underline">
                {{ user.name }}
            </span>
            <span class="mx-1 opacity-60">·</span>
            <span class="text-xs opacity-60"> @{{ user.username }} </span>
        </a>
        <RouterLink v-else :to="`/profile/${user.username}`">
            <span class="font-bold hover:underline">
                {{ user.name }}
            </span>
            <span class="mx-1 opacity-60">·</span>
            <span class="text-xs opacity-60"> @{{ user.username }} </span>
        </RouterLink>
        <p v-if="!isRemote" class="opacity-60">
            {{ user.statistics.followersCount }}
            {{ t('profile.stats.followers', user.statistics.followersCount) }}
        </p>
    </div>
    <div>
        <FollowButton v-if="!isRemote" :user="user" />
    </div>
</template>
