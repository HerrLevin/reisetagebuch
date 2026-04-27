import {
    ActivityPubUserFollowedData,
    NotificationType,
    NotificationWrapper,
    PostLikedData,
    TraewellingCrosspostFailedData,
    UserFollowedData,
    UserRequestedFollowData,
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

export const isUserRequestedFollowNotification = (
    notification: NotificationWrapper,
) => {
    const data = notification.data;
    return (
        notification.data !== null &&
        notification.type ===
            NotificationType.UserRequestedFollowNotification &&
        (data as UserRequestedFollowData).followerUserId !== undefined
    );
};

export const isTraewellingCrosspostFailedNotification = (
    notification: NotificationWrapper,
) => {
    const data = notification.data;
    return (
        notification.data !== null &&
        notification.type ===
            NotificationType.TraewellingCrosspostFailedNotification &&
        (data as TraewellingCrosspostFailedData).errorMessage !== undefined
    );
};

export const isActivityPubUserFollowedNotification = (
    notification: NotificationWrapper,
) => {
    const data = notification.data;
    return (
        notification.data !== null &&
        notification.type ===
            NotificationType.ActivityPubUserFollowedNotification &&
        (data as ActivityPubUserFollowedData).followerActorId !== undefined
    );
};

export const getTypedNotificationData = (
    notification: NotificationWrapper,
): PostLikedData | UserFollowedData | ActivityPubUserFollowedData | null => {
    if (isPostLikedNotification(notification)) {
        return notification.data;
    }
    if (isUserFollowedNotification(notification)) {
        return notification.data;
    }
    if (isTraewellingCrosspostFailedNotification(notification)) {
        return notification.data;
    }
    if (isUserRequestedFollowNotification(notification)) {
        return notification.data;
    }
    if (isActivityPubUserFollowedNotification(notification)) {
        return notification.data;
    }
    return null;
};
