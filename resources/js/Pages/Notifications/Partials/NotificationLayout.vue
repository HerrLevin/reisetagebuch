<script setup lang="ts">
import type {
    RemotePostBoostedData,
    RemotePostLikedData,
    RemotePostRepliedData,
} from '@/types/activitypub';
import {
    isPostLikedNotification,
    isRemotePostBoostedNotification,
    isRemotePostLikedNotification,
    isRemotePostRepliedNotification,
    isTraewellingCrosspostFailedNotification,
    isUserFollowedNotification,
} from '@/types/notifications';
import { PropType } from 'vue';
import { RouterLink } from 'vue-router';
import {
    NotificationWrapper,
    PostLikedData,
    TraewellingCrosspostFailedData,
    UserFollowedData,
} from '../../../../types/Api.gen';

defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const getNotificationLink = (notification: NotificationWrapper) => {
    if (isPostLikedNotification(notification)) {
        const data = notification.data as PostLikedData;
        return {
            name: 'posts.show',
            params: {
                postId: data.postId,
            },
        };
    }
    if (isUserFollowedNotification(notification)) {
        const data = notification.data as UserFollowedData;
        return {
            name: 'profile.show',
            params: {
                username: data.followerUserName,
            },
        };
    }
    if (isTraewellingCrosspostFailedNotification(notification)) {
        const data = notification.data as TraewellingCrosspostFailedData;
        return {
            name: 'posts.show',
            params: {
                postId: data.postId,
            },
        };
    }
    if (isRemotePostLikedNotification(notification)) {
        const data = notification.data as unknown as RemotePostLikedData;
        return {
            name: 'posts.show',
            params: {
                postId: data.postId,
            },
        };
    }
    if (isRemotePostBoostedNotification(notification)) {
        const data = notification.data as unknown as RemotePostBoostedData;
        return {
            name: 'posts.show',
            params: {
                postId: data.postId,
            },
        };
    }
    if (isRemotePostRepliedNotification(notification)) {
        const data = notification.data as unknown as RemotePostRepliedData;
        return {
            name: 'posts.show',
            params: {
                postId: data.postId,
            },
        };
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
