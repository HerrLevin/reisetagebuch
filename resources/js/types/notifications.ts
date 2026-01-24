import {
    NotificationType,
    NotificationWrapper,
    PostLikedData,
} from '../../types/Api.gen';

export const isPostLikedNotification = (notification: NotificationWrapper) => {
    const data = notification.data;
    return (
        notification.data !== null &&
        notification.type === NotificationType.PostLikedNotification &&
        (data as PostLikedData).likedByUserId !== undefined
    );
};

export const getTypedNotificationData = (
    notification: NotificationWrapper,
): PostLikedData | null => {
    if (isPostLikedNotification(notification)) {
        return notification.data;
    }
    return null;
};
