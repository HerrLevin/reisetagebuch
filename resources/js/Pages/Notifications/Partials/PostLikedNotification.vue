<script setup lang="ts">
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import { Heart } from 'lucide-vue-next';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { NotificationWrapper, PostLikedData } from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const data = props.notification.data as PostLikedData;
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
                            v-if="data.likedByUserAvatarUrl"
                            :src="data.likedByUserAvatarUrl"
                            :alt="data.likedByUserName"
                        />
                    </div>
                </div>
                {{
                    t('notifications.liked.lead', {
                        user: data.likedByUserDisplayName,
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
