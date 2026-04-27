<script setup lang="ts">
import {
    isActivityPubPostLikedNotification,
    isActivityPubUserFollowedNotification,
    isPostLikedNotification,
    isTraewellingCrosspostFailedNotification,
    isUserFollowedNotification,
} from '@/types/notifications';
import { computed, PropType } from 'vue';
import { RouterLink } from 'vue-router';
import {
    ActivityPubPostLikedData,
    ActivityPubUserFollowedData,
    NotificationWrapper,
    PostLikedData,
    TraewellingCrosspostFailedData,
    UserFollowedData,
} from '../../../../types/Api.gen';

const props = defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const getNotificationLink = (
    notification: NotificationWrapper,
): string | { name: string; params: Record<string, string> } => {
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
    if (isActivityPubUserFollowedNotification(notification)) {
        const data = notification.data as ActivityPubUserFollowedData;
        return data.followerProfileUrl ?? '#';
    }
    if (isActivityPubPostLikedNotification(notification)) {
        const data = notification.data as ActivityPubPostLikedData;
        if (data.postId) {
            return {
                name: 'posts.show',
                params: {
                    postId: data.postId,
                },
            };
        }
        return '#';
    }
    return '#';
};

const link = computed(() => getNotificationLink(props.notification));
const isExternalLink = computed(
    () => typeof link.value === 'string' && link.value !== '#',
);
</script>
<template>
    <a
        v-if="isExternalLink"
        :href="link as string"
        target="_blank"
        rel="noopener noreferrer"
    >
        <li
            :class="{
                'bg-base-200': !notification.readAt,
            }"
            class="list-row hover-list-entry cursor-pointer"
        >
            <slot></slot>
        </li>
    </a>
    <RouterLink
        v-else
        v-slot="{ navigate }"
        class="list-row hover-list-entry cursor-pointer"
        custom
        :to="link"
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
