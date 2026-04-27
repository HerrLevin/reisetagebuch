<script setup lang="ts">
import { useTitle } from '@/composables/useTitle';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ActivityPubPostLikedNotification from '@/Pages/Notifications/Partials/ActivityPubPostLikedNotification.vue';
import ActivityPubUserFollowedNotification from '@/Pages/Notifications/Partials/ActivityPubUserFollowedNotification.vue';
import PostLikedNotification from '@/Pages/Notifications/Partials/PostLikedNotification.vue';
import TraewellingCrosspostFailedNotification from '@/Pages/Notifications/Partials/TraewellingCrosspostFailedNotification.vue';
import UserFollowedNotification from '@/Pages/Notifications/Partials/UserFollowedNotification.vue';
import UserRequestedFollowNotification from '@/Pages/Notifications/Partials/UserRequestedFollowNotification.vue';
import { useNotificationStore } from '@/stores/notifications';
import {
    getTypedNotificationData,
    isActivityPubPostLikedNotification,
    isActivityPubUserFollowedNotification,
    isPostLikedNotification,
    isTraewellingCrosspostFailedNotification,
    isUserFollowedNotification,
    isUserRequestedFollowNotification,
} from '@/types/notifications';
import { storeToRefs } from 'pinia';
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { NotificationWrapper } from '../../../types/Api.gen';

const { t } = useI18n();

useTitle(t('notifications.title'));

const loadingNotifications = ref(false);
const store = useNotificationStore();
const { notifications, unreadCount, loading } = storeToRefs(store);

const getNotificationComponent = (notification: NotificationWrapper) => {
    if (isPostLikedNotification(notification)) {
        return PostLikedNotification;
    }
    if (isUserFollowedNotification(notification)) {
        return UserFollowedNotification;
    }
    if (isTraewellingCrosspostFailedNotification(notification)) {
        return TraewellingCrosspostFailedNotification;
    }
    if (isUserRequestedFollowNotification(notification)) {
        return UserRequestedFollowNotification;
    }
    if (isActivityPubUserFollowedNotification(notification)) {
        return ActivityPubUserFollowedNotification;
    }
    if (isActivityPubPostLikedNotification(notification)) {
        return ActivityPubPostLikedNotification;
    }
    return null;
};

const handleNotificationClick = (notification: NotificationWrapper) => {
    if (!notification.readAt) {
        store.markAsRead(notification.id);
    }
};

onMounted(() => {
    store.fetchNotifications();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="card bg-base-100 min-w-full p-0 shadow-md">
            <div class="card-body">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="card-title text-base">
                        {{ t('notifications.title') }}
                    </h3>
                    <button
                        v-if="unreadCount > 0"
                        class="btn btn-ghost btn-xs"
                        :disabled="loading"
                        @click="store.markAllAsRead()"
                    >
                        {{ t('notifications.mark_all_read') }}
                    </button>
                </div>

                <span
                    v-if="loadingNotifications"
                    class="loading loading-dots loading-xs"
                ></span>
                <div
                    v-if="notifications.length === 0 && !loadingNotifications"
                    class="py-8 text-center text-sm opacity-60"
                >
                    {{ t('notifications.none') }}
                </div>

                <ul v-else class="list p-0">
                    <template
                        v-for="notification in notifications"
                        :key="notification.id"
                    >
                        <component
                            :is="getNotificationComponent(notification)"
                            v-if="
                                getTypedNotificationData(notification) !== null
                            "
                            :notification="notification"
                            @click="handleNotificationClick(notification)"
                        ></component>
                    </template>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
