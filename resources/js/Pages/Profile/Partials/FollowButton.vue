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
</script>

<template>
    <button
        v-if="authUser.user && authUser.user.id !== user.id"
        class="btn rounded-full"
        :class="{ 'btn-primary': !followed }"
        @click="toggleFollow"
    >
        <Plus v-if="!followed" class="size-4" />
        {{ followed ? t('profile.unfollow') : t('profile.follow') }}
    </button>
</template>
