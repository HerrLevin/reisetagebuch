<script setup lang="ts">
import NotificationLayout from '@/Pages/Notifications/Partials/NotificationLayout.vue';
import { GlobeX } from 'lucide-vue-next';
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
        </div>
        <div
            v-if="!notification.readAt"
            class="badge badge-primary badge-xs"
        ></div>
    </NotificationLayout>
</template>
