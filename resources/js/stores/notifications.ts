import { api } from '@/api';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { NotificationWrapper } from '../../types/Api.gen';

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref<NotificationWrapper[]>([]);
    const unreadCount = ref(0);
    const loadingMarkAllRead = ref(false);
    const loadingNotifications = ref(false);

    const fetchNotifications = async () => {
        loadingNotifications.value = true;
        api.notifications
            .listNotifications()
            .then((response) => {
                notifications.value = response.data;
                loadingNotifications.value = false;
            })
            .catch((error) => {
                loadingNotifications.value = false;
                console.error('Error fetching notifications:', error);
            });
    };

    const fetchUnreadCount = async () => {
        api.notifications
            .unreadNotificationCount()
            .then((response) => {
                unreadCount.value = response.data.count;
            })
            .catch((error) => {
                console.error('Error fetching unread count:', error);
            });
    };

    const markAsRead = async (id: string) => {
        try {
            await api.notifications.markNotificationAsRead(id);

            const notification = notifications.value.find((n) => n.id === id);
            if (notification) {
                notification.readAt = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        loadingMarkAllRead.value = true;
        try {
            await api.notifications.markAllNotificationsAsRead();

            notifications.value.forEach((n) => {
                n.readAt = new Date().toISOString();
            });
            unreadCount.value = 0;
        } catch (error) {
            console.error('Error marking all as read:', error);
        } finally {
            loadingMarkAllRead.value = false;
        }
    };

    return {
        notifications,
        unreadCount,
        loading: loadingMarkAllRead,
        fetchNotifications,
        fetchUnreadCount,
        markAsRead,
        markAllAsRead,
    };
});
