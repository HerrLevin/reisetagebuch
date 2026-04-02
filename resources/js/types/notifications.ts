import {
    NotificationType,
    NotificationWrapper,
    PostLikedData,
    TraewellingCrosspostFailedData,
    UserFollowedData,
    UserRequestedFollowData,
} from '../../types/Api.gen';
import type {
    RemotePostBoostedData,
    RemotePostLikedData,
    RemotePostRepliedData,
    RemoteUserFollowedData,
} from './activitypub';

// Extend NotificationType with remote notification types
// These are not in the generated enum yet, so we use string comparison
const REMOTE_POST_LIKED = 'RemotePostLikedNotification';
const REMOTE_POST_BOOSTED = 'RemotePostBoostedNotification';
const REMOTE_POST_REPLIED = 'RemotePostRepliedNotification';
const REMOTE_USER_FOLLOWED = 'RemoteUserFollowedNotification';

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

export const isRemotePostLikedNotification = (
    notification: NotificationWrapper,
) => {
    return (
        notification.data !== null &&
        (notification.type as string) === REMOTE_POST_LIKED &&
        (notification.data as unknown as RemotePostLikedData).actorUsername !==
            undefined
    );
};

export const isRemotePostBoostedNotification = (
    notification: NotificationWrapper,
) => {
    return (
        notification.data !== null &&
        (notification.type as string) === REMOTE_POST_BOOSTED &&
        (notification.data as unknown as RemotePostBoostedData)
            .actorUsername !== undefined
    );
};

export const isRemotePostRepliedNotification = (
    notification: NotificationWrapper,
) => {
    return (
        notification.data !== null &&
        (notification.type as string) === REMOTE_POST_REPLIED &&
        (notification.data as unknown as RemotePostRepliedData)
            .actorUsername !== undefined
    );
};

export const isRemoteUserFollowedNotification = (
    notification: NotificationWrapper,
) => {
    return (
        notification.data !== null &&
        (notification.type as string) === REMOTE_USER_FOLLOWED &&
        (notification.data as unknown as RemoteUserFollowedData)
            .actorUsername !== undefined
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
    if (isTraewellingCrosspostFailedNotification(notification)) {
        return notification.data;
    }
    if (isUserRequestedFollowNotification(notification)) {
        return notification.data;
    }
    if (isRemotePostLikedNotification(notification)) {
        return notification.data;
    }
    if (isRemotePostBoostedNotification(notification)) {
        return notification.data;
    }
    if (isRemotePostRepliedNotification(notification)) {
        return notification.data;
    }
    if (isRemoteUserFollowedNotification(notification)) {
        return notification.data;
    }
    return null;
};
