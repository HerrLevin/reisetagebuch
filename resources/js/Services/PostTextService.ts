import { AllPosts, isLocationPost, isTransportPost } from '@/types/PostTypes';
import { DateTime } from 'luxon';

export function getBaseText(post: AllPosts): string {
    if (isLocationPost(post)) {
        return `at ${post.location.name}!`;
    } else if (isTransportPost(post)) {
        return `from ${post.originStop.location.name} to ${post.originStop.location.name}`;
    }

    return '';
}

export function getShareText(post: AllPosts): string {
    return `Check out ${post.user.username}'s post ${getBaseText(post)}`;
}

export function getOwnShareText(post: AllPosts): string {
    return `Check out my post ${getBaseText(post)}`;
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
