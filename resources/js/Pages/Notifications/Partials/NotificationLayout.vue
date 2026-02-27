<script setup lang="ts">
import { isPostLikedNotification } from '@/types/notifications';
import { PropType } from 'vue';
import { RouterLink } from 'vue-router';
import { NotificationWrapper } from '../../../../types/Api.gen';

defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const getNotificationLink = (notification: NotificationWrapper): string => {
    if (isPostLikedNotification(notification)) {
        return `/posts/${notification.data!.postId}`;
    }
    return '#';
};
</script>
<template>
    <RouterLink
        v-slot="{ navigate }"
        class="list-row hover-list-entry cursor-pointer"
        custom
        :to="getNotificationLink(notification)"
    >
        <li
            :class="{
                'bg-base-200': !notification.readAt,
            }"
            class="list-row hover-list-entry cursor-pointer"
            @click="navigate"
        >
            <slot></slot>
        </li>
    </RouterLink>
</template>
