<script setup lang="ts">
import { api } from '@/api';
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import { useUserStore } from '@/stores/user';
import { User } from 'lucide-vue-next';
import { PropType, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    NotificationWrapper,
    UserRequestedFollowData,
} from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const authUser = useUserStore();
const hide = ref(false);

function approve() {
    if (!authUser.user) return;
    api.users
        .approveFollowRequest(data.followerUserId, authUser.user.id)
        .then(() => {
            hide.value = true;
        });
}

function decline() {
    if (!authUser.user) return;
    api.users
        .deleteFollowRequest(data.followerUserId, authUser.user.id)
        .then(() => {
            hide.value = true;
        });
}

const data = props.notification.data as UserRequestedFollowData;
</script>
<template>
    <NotificationLayout v-show="!hide" :notification="notification">
        <div>
            <User></User>
        </div>
        <div class="flex-1">
            <div class="text-sm">
                <div class="avatar">
                    <div class="w-4 rounded">
                        <img
                            v-if="data.followerUserAvatarUrl"
                            :src="data.followerUserAvatarUrl"
                            :alt="data.followerUserName"
                        />
                    </div>
                </div>
                {{
                    t('notifications.follow_request.lead', {
                        user: data.followerUserDisplayName,
                    })
                }}

                <div class="join my-1 ms-5 sm:my-0">
                    <button
                        class="btn btn-outline btn-error btn-xs join-item"
                        @click="decline()"
                    >
                        {{ t('notifications.follow_request.decline') }}
                    </button>
                    <button
                        class="btn btn-success btn-xs join-item"
                        @click="approve()"
                    >
                        {{ t('notifications.follow_request.accept') }}
                    </button>
                </div>
            </div>
            <div class="mt-1 text-xs opacity-40">
                {{ new Date(notification.createdAt).toLocaleString() }}
            </div>
        </div>
        <div
            v-if="!notification.readAt"
            class="badge badge-primary badge-xs"
        ></div>
    </NotificationLayout>
</template>
