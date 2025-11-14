<script setup lang="ts">
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import { PostLikedNotification } from '@/types/notifications';
import { Heart } from 'lucide-vue-next';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    notification: {
        type: Object as PropType<PostLikedNotification>,
        required: true,
    },
});
</script>
<template>
    <NotificationLayout :notification="notification">
        <div>
            <Heart class="fill-red-500 text-red-500"></Heart>
        </div>
        <div class="flex-1">
            <div class="text-sm">
                <div class="avatar">
                    <div class="w-4 rounded">
                        <img
                            v-if="notification.data.liker.avatar"
                            :src="notification.data.liker.avatar"
                            :alt="notification.data.liker.username"
                        />
                    </div>
                </div>
                {{
                    t('notifications.liked.lead', {
                        user: notification.data.liker.name,
                    })
                }}
            </div>
            <div
                v-if="notification.data.post_body"
                class="line-clamp-1 text-xs opacity-60"
            >
                {{ notification.data.post_body }}
            </div>
            <div class="mt-1 text-xs opacity-40">
                {{ new Date(notification.created_at).toLocaleString() }}
            </div>
        </div>
        <div
            v-if="!notification.read_at"
            class="badge badge-primary badge-xs"
        ></div>
    </NotificationLayout>
</template>
