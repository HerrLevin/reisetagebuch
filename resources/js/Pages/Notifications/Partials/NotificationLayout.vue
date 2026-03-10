<script setup lang="ts">
import {
    isPostLikedNotification,
    isUserFollowedNotification,
} from '@/types/notifications';
import { PropType } from 'vue';
import { RouterLink } from 'vue-router';
import {
    NotificationWrapper,
    PostLikedData,
    UserFollowedData,
} from '../../../../types/Api.gen';

defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const getNotificationLink = (notification: NotificationWrapper): string => {
    if (isPostLikedNotification(notification)) {
        const data = notification.data as PostLikedData;
        return `/posts/${data.postId}`;
    }
    if (isUserFollowedNotification(notification)) {
        const data = notification.data as UserFollowedData;
        return `/profile/${data.followerUserName}`;
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
