<script setup lang="ts">
import { isPostLikedNotification, Notification } from '@/types/notifications';
import { Link } from '@inertiajs/vue3';
import { PropType } from 'vue';

defineProps({
    notification: {
        type: Object as PropType<Notification>,
        required: true,
    },
});

const getNotificationLink = (notification: Notification): string => {
    if (isPostLikedNotification(notification)) {
        return route('posts.show', notification.data.post_id);
    }
    return '#';
};
</script>
<template>
    <Link
        class="list-row hover-list-entry cursor-pointer"
        as="li"
        :class="{
            'bg-base-200': !notification.read_at,
        }"
        :href="getNotificationLink(notification)"
    >
        <slot></slot>
    </Link>
</template>
