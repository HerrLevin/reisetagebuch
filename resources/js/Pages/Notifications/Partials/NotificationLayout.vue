<script setup lang="ts">
import { isPostLikedNotification } from '@/types/notifications';
import { Link } from '@inertiajs/vue3';
import { PropType } from 'vue';
import { NotificationWrapper } from '../../../../types/Api.gen';

defineProps({
    notification: {
        type: Object as PropType<NotificationWrapper>,
        required: true,
    },
});

const getNotificationLink = (notification: NotificationWrapper): string => {
    if (isPostLikedNotification(notification)) {
        return route('posts.show', notification.data!.postId);
    }
    return '#';
};
</script>
<template>
    <Link
        class="list-row hover-list-entry cursor-pointer"
        as="li"
        :class="{
            'bg-base-200': !notification.readAt,
        }"
        :href="getNotificationLink(notification)"
    >
        <slot></slot>
    </Link>
</template>
