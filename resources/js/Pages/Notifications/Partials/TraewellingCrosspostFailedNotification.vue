<script setup lang="ts">
import LoadingButton from '@shared/Components/LoadingButton.vue';
import { api } from '@/api';
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import { useNotificationStore } from '@/stores/notifications';
import { GlobeX, RefreshCw } from 'lucide-vue-next';
import { PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    NotificationWrapper,
    TraewellingCrosspostFailedData,
} from '../../../../types/Api.gen';

const { t } = useI18n();

const props = defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const data = props.notification.data as TraewellingCrosspostFailedData;
const store = useNotificationStore();
const retry = async () => {
    await api.instance.post(`/posts/${data.postId}/traewelling/retry`);
    store.removeNotification(props.notification.id);
};
</script>
<template>
    <NotificationLayout :notification="notification">
        <div>
            <GlobeX class="fill-error text-error"></GlobeX>
        </div>
        <div class="flex-1">
            <div class="text-sm">
                {{ t('notifications.crosspost_failed.lead') }}
            </div>
            <div class="line-clamp-2 text-xs opacity-60">
                {{
                    t('notifications.crosspost_failed.info', {
                        error: data.errorMessage,
                    })
                }}
            </div>
            <div class="mt-1 text-xs opacity-40">
                {{ new Date(notification.createdAt).toLocaleString() }}
            </div>
            <LoadingButton
                class="btn btn-xs btn-ghost mt-2 gap-1"
                :action="retry"
                :icon="RefreshCw"
                icon-class="h-3 w-3"
                @click.stop
            >
                {{ t('notifications.crosspost_failed.retry') }}
            </LoadingButton>
        </div>
        <div
            v-if="!notification.readAt"
            class="badge badge-primary badge-xs"
        ></div>
    </NotificationLayout>
</template>
