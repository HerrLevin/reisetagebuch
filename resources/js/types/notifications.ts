import { UserDto } from '@/types/index';

export type Notification = {
    id: string;
    type: NotificationType;
    data: object;
    read_at: string | null;
    created_at: string;
    updated_at: string;
};

export type PostLikedNotification = Notification & {
    type: NotificationType.PostLiked;
    data: {
        liker: UserDto;
        post_id: string;
        post_body: string | null;
    };
};

export enum NotificationType {
    PostLiked = 'post-liked',
}

export const isPostLikedNotification = (
    notification: Notification,
): notification is PostLikedNotification => {
    return (
        notification.type === NotificationType.PostLiked &&
        (notification as PostLikedNotification).data.liker !== undefined
    );
};

export const getTypedNotification = (
    notification: Notification,
): PostLikedNotification | null => {
    if (isPostLikedNotification(notification)) {
        return notification;
    }
    return null;
};
