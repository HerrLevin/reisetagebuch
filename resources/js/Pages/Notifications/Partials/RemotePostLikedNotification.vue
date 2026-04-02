<script setup lang="ts">
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import type { RemotePostLikedData } from '@/types/activitypub';
import { Heart } from 'lucide-vue-next';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { NotificationWrapper } from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const data = props.notification.data as unknown as RemotePostLikedData;
</script>
<template>
    <NotificationLayout :notification="notification">
        <div>
            <Heart class="fill-red-500 text-red-500" />
        </div>
        <div class="flex-1">
            <div class="text-sm">
                <div class="avatar">
                    <div class="w-4 rounded">
                        <img
                            v-if="data.actorAvatar"
                            :src="data.actorAvatar"
                            :alt="data.actorUsername"
                        />
                    </div>
                </div>
                {{
                    t('notifications.remote_liked.lead', {
                        user: data.actorDisplayName || data.actorUsername,
                        instance: data.actorInstance,
                    })
                }}
            </div>
            <div v-if="data.postBody" class="line-clamp-1 text-xs opacity-60">
                {{ data.postBody }}
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
