<script setup lang="ts">
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import { UserPlus } from 'lucide-vue-next';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    NotificationWrapper,
    UserFollowedData,
} from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const data = props.notification.data as UserFollowedData;
</script>
<template>
    <NotificationLayout :notification="notification">
        <div>
            <UserPlus></UserPlus>
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
                    t('notifications.follower.lead', {
                        user: data.followerUserDisplayName,
                    })
                }}
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
