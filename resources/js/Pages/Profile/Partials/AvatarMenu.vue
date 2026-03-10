<script setup lang="ts">
import FollowButton from '@/Pages/Profile/Partials/FollowButton.vue';
import ProfileEditModal from '@/Pages/Profile/Partials/ProfileEditModal.vue';
import { useUserStore } from '@/stores/user';
import { EllipsisVertical } from 'lucide-vue-next';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { UserDto } from '../../../../types/Api.gen';

const { t } = useI18n();

const authUser = useUserStore();
const emits = defineEmits(['profile-updated']);

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
                    :alt="t('posts.profile_picture_alt', { name: user.name })"
                />
            </div>
        </div>
        <div class="flex items-center gap-2">
            <template v-if="authUser.user">
                <ProfileEditModal
                    :user="user"
                    @profile-updated="emits('profile-updated')"
                />
                <FollowButton
                    :user="user"
                    @follow-updated="emits('profile-updated')"
                />
            </template>
            <button class="btn btn-circle" type="button">
                <EllipsisVertical />
            </button>
        </div>
    </div>
</template>
