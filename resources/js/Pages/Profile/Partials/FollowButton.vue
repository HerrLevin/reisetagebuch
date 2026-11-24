<script setup lang="ts">
import LoadingButton from '@shared/Components/LoadingButton.vue';
import { api } from '@/api';
import { useUserStore } from '@/stores/user';
import { Plus } from 'lucide-vue-next';
import { PropType, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../../types/Api.gen';

const { t } = useI18n();
const authUser = useUserStore();
const emits = defineEmits(['follow-updated']);
const followed = ref<boolean | null>(null);
const followRequested = ref<boolean | null>(null);

const props = defineProps({
    user: {
        type: Object as PropType<UserDto>,
        default: () => ({}),
    },
});

function toggleFollow() {
    return followed.value ? unfollow() : follow();
}

function toggleFollowRequest() {
    return followRequested.value ? destroyFollowRequest() : requestFollow();
}

function requestFollow() {
    if (!authUser.user) {
        return;
    }
    return api.users
        .createFollowRequest(authUser.user.id, props.user.id)
        .then(() => {
            emits('follow-updated', true);
            followRequested.value = true;
        });
}

function destroyFollowRequest() {
    if (!authUser.user) {
        return;
    }
    return api.users
        .deleteFollowRequest(authUser.user.id, props.user.id)
        .then(() => {
            emits('follow-updated', false);
            followRequested.value = false;
        });
}

function follow() {
    if (!authUser.user) {
        return;
    }
    return api.users.createFollow(authUser.user.id, props.user.id).then(() => {
        emits('follow-updated', true);
        followed.value = true;
    });
}

function unfollow() {
    if (!authUser.user) {
        return;
    }
    return api.users.deleteFollow(authUser.user.id, props.user.id).then(() => {
        emits('follow-updated', false);
        followed.value = false;
    });
}

watch(
    () => props.user.isFollowed,
    (newVal) => {
        followed.value = newVal || null;
    },
    { immediate: true },
);
watch(
    () => props.user.isFollowRequested,
    (newVal) => {
        followRequested.value = newVal || null;
    },
    { immediate: true },
);
</script>

<template>
    <template v-if="authUser.user && authUser.user.id !== user.id">
        <LoadingButton
            v-if="
                !user.requiresFollowRequest ||
                (user.requiresFollowRequest && followed)
            "
            class="btn rounded-full"
            :class="{ 'btn-primary': !followed }"
            :action="toggleFollow"
            :icon="followed ? undefined : Plus"
        >
            {{ followed ? t('profile.unfollow') : t('profile.follow') }}
        </LoadingButton>
        <LoadingButton
            v-else
            class="btn rounded-full"
            :class="{ 'btn-gray': followRequested }"
            :action="toggleFollowRequest"
            :icon="followRequested ? undefined : Plus"
        >
            {{
                followRequested
                    ? t('profile.withdraw_follow_request')
                    : t('profile.request_follow')
            }}
        </LoadingButton>
    </template>
</template>
