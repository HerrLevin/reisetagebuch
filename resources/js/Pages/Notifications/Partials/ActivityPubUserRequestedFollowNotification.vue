<script setup lang="ts">
import { api } from '@/api';
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import { useUserStore } from '@/stores/user';
import { UserPlus } from 'lucide-vue-next';
import { PropType, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    ActivityPubUserRequestedFollowData,
    NotificationWrapper,
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

const data = props.notification.data as ActivityPubUserRequestedFollowData;
const displayName = data.followerDisplayName || data.followerPreferredUsername;

function approve() {
    if (!authUser.user) return;
    api.users
        .approveActivityPubFollowRequest(authUser.user.id, data.followRequestId)
        .then(() => {
            hide.value = true;
        });
}

function decline() {
    if (!authUser.user) return;
    api.users
        .rejectActivityPubFollowRequest(authUser.user.id, data.followRequestId)
        .then(() => {
            hide.value = true;
        });
}
</script>
<template>
    <NotificationLayout v-show="!hide" :notification="notification">
        <div>
            <UserPlus></UserPlus>
        </div>
        <div class="flex-1">
            <div class="text-sm">
                <div class="avatar">
                    <div class="w-4 rounded">
                        <img
                            v-if="data.followerIconUrl"
                            :src="data.followerIconUrl"
                            :alt="displayName"
                        />
                    </div>
                </div>
                {{
                    t('notifications.ap_follow_request.lead', {
                        user: displayName,
                    })
                }}

                <div class="join my-1 ms-5 sm:my-0">
                    <button
                        class="btn btn-outline btn-error btn-xs join-item"
                        @click="decline()"
                    >
                        {{ t('notifications.ap_follow_request.decline') }}
                    </button>
                    <button
                        class="btn btn-success btn-xs join-item"
                        @click="approve()"
                    >
                        {{ t('notifications.ap_follow_request.accept') }}
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
