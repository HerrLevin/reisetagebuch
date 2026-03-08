<script setup lang="ts">
import { api } from '@/api';
import { useUserStore } from '@/stores/user';
import { Plus } from 'lucide-vue-next';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../../types/Api.gen';

const { t } = useI18n();
const authUser = useUserStore();
const emits = defineEmits(['follow-updated']);

const props = defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});

function toggleFollow() {
    if (props.user.isFollowed) {
        unfollow();
    } else {
        follow();
    }
}

function follow() {
    if (!authUser.user) {
        return;
    }
    api.users.createFollow(authUser.user.id, props.user.id).then(() => {
        emits('follow-updated', true);
    });
}

function unfollow() {
    if (!authUser.user) {
        return;
    }
    api.users.deleteFollow(authUser.user.id, props.user.id).then(() => {
        emits('follow-updated', false);
    });
}
</script>

<template>
    <button
        v-if="authUser.user && authUser.user.id !== user.id"
        class="btn rounded-full"
        :class="{ 'btn-primary': !user.isFollowed }"
        @click="toggleFollow"
    >
        <Plus v-if="!user.isFollowed" class="size-4" />
        {{ user.isFollowed ? t('profile.unfollow') : t('profile.follow') }}
    </button>
</template>
