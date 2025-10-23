import i18n from '@/i18n';
import { AllPosts, isLocationPost, isTransportPost } from '@/types/PostTypes';
import { DateTime } from 'luxon';

const { t } = i18n.global;

export function getBaseText(post: AllPosts): string {
    if (isLocationPost(post)) {
        return t('share.messages.at', {
            location: post.location.name,
        });
    } else if (isTransportPost(post)) {
        return t('share.messages.from_to', {
            from: post.originStop.location.name,
            to: post.destinationStop.location.name,
        });
    }

    return '';
}

export function getShareText(post: AllPosts): string {
    return t('share.messages.general_share', {
        username: post.user.username,
        baseText: getBaseText(post),
    });
}

export function getOwnShareText(post: AllPosts): string {
    return t('share.messages.personal_share', {
        baseText: getBaseText(post),
    });
}

export function prettyDates(post: AllPosts): string {
    if (isTransportPost(post)) {
        const departure: DateTime<true> | DateTime<false> = DateTime.fromISO(
            post.originStop.departureTime || '',
        );
        const arrival: DateTime<true> | DateTime<false> = DateTime.fromISO(
            post.destinationStop.arrivalTime || '',
        );
        if (departure.isValid && arrival.isValid) {
            return `${departure.toLocaleString(DateTime.DATETIME_SHORT)} – ${arrival.toLocaleString(DateTime.DATETIME_SHORT)}`;
        }
    }

    return `@ ${getDisplayDate(post.created_at)}`;
}

export function getDisplayDate(dateString: string): string {
    const date: DateTime<true> | DateTime<false> = DateTime.fromISO(dateString);

    if (date.diffNow('days').days < -1) {
        return date.toLocaleString(DateTime.DATETIME_SHORT);
    }
    return date.toRelative() || '';
}
