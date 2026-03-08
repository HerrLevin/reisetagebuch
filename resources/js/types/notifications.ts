import {
    NotificationType,
    NotificationWrapper,
    PostLikedData,
    UserFollowedData,
} from '../../types/Api.gen';

export const isPostLikedNotification = (notification: NotificationWrapper) => {
    const data = notification.data;
    return (
        notification.data !== null &&
        notification.type === NotificationType.PostLikedNotification &&
        (data as PostLikedData).likedByUserId !== undefined
    );
};

export const isUserFollowedNotification = (
    notification: NotificationWrapper,
) => {
    const data = notification.data;
    return (
        notification.data !== null &&
        notification.type === NotificationType.UserFollowedNotification &&
        (data as UserFollowedData).followerUserId !== undefined
    );
};

export const getTypedNotificationData = (
    notification: NotificationWrapper,
): PostLikedData | UserFollowedData | null => {
    if (isPostLikedNotification(notification)) {
        return notification.data;
    }
    if (isUserFollowedNotification(notification)) {
        return notification.data;
    }
    return null;
};
