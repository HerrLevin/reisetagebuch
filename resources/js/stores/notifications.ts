import { Notification } from '@/types/notifications';
import axios from 'axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref<Notification[]>([]);
    const unreadCount = ref(0);
    const loadingMarkAllRead = ref(false);
    const loadingNotifications = ref(false);

    const fetchNotifications = async () => {
        loadingNotifications.value = true;
        axios
            .get(route('notifications.index'))
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
        axios
            .get('/api/notifications/unread-count')
            .then((response) => {
                unreadCount.value = response.data.count;
            })
            .catch((error) => {
                console.error('Error fetching unread count:', error);
            });
    };

    const markAsRead = async (id: string) => {
        try {
            await axios.post(route('notifications.read', id));

            const notification = notifications.value.find((n) => n.id === id);
            if (notification) {
                notification.read_at = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        loadingMarkAllRead.value = true;
        try {
            await axios.post(route('notifications.read-all'));

            notifications.value.forEach((n) => {
                n.read_at = new Date().toISOString();
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
