<script setup lang="ts">
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
    if (followed.value) {
        unfollow();
    } else {
        follow();
    }
}

function toggleFollowRequest() {
    if (followRequested.value) {
        destroyFollowRequest();
    } else {
        requestFollow();
    }
}

function requestFollow() {
    if (!authUser.user) {
        return;
    }
    api.users.createFollowRequest(authUser.user.id, props.user.id).then(() => {
        emits('follow-updated', true);
        followRequested.value = true;
    });
}

function destroyFollowRequest() {
    if (!authUser.user) {
        return;
    }
    api.users.deleteFollowRequest(authUser.user.id, props.user.id).then(() => {
        emits('follow-updated', false);
        followRequested.value = false;
    });
}

function follow() {
    if (!authUser.user) {
        return;
    }
    api.users.createFollow(authUser.user.id, props.user.id).then(() => {
        emits('follow-updated', true);
        followed.value = true;
    });
}

function unfollow() {
    if (!authUser.user) {
        return;
    }
    api.users.deleteFollow(authUser.user.id, props.user.id).then(() => {
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
        <button
            v-if="
                !user.requiresFollowRequest ||
                (user.requiresFollowRequest && followed)
            "
            class="btn rounded-full"
            :class="{ 'btn-primary': !followed }"
            @click="toggleFollow"
        >
            <Plus v-if="!followed" class="size-4" />
            {{ followed ? t('profile.unfollow') : t('profile.follow') }}
        </button>
        <button
            v-else
            class="btn rounded-full"
            :class="{ 'btn-gray': followRequested }"
            @click="toggleFollowRequest"
        >
            <Plus v-if="!followRequested" class="size-4" />
            {{
                followRequested
                    ? t('profile.withdraw_follow_request')
                    : t('profile.request_follow')
            }}
        </button>
    </template>
</template>
