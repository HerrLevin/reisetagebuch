import i18n from '@/i18n';
import { isApiLocationPost, isApiTransportPost } from '@/types/PostTypes';
import { DateTime } from 'luxon';
import { BasePost, LocationPost, TransportPost } from '../../types/Api.gen';

const { t } = i18n.global;

type AllPosts = BasePost | LocationPost | TransportPost;

export function getBaseText(post: AllPosts): string {
    if (isApiLocationPost(post)) {
        return t('share.messages.at', {
            location: post.location.name,
        });
    } else if (isApiTransportPost(post)) {
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
    if (isApiTransportPost(post)) {
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
