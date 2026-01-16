import { Stop, TransportPost } from '@/types/PostTypes';
import { DateTime, DateTimeMaybeValid } from 'luxon';
import { TransportPost as ApiTransportPost } from '../../types/Api.gen';

export function calculateDelay(
    plannedTime: DateTimeMaybeValid | null,
    manualTime: string | null,
    delay: number | null = null,
): number | null {
    if (manualTime && plannedTime) {
        const delay =
            new Date(manualTime).getTime() - plannedTime.toJSDate().getTime();
        if (delay < -60000) {
            return Math.floor(delay / 60000);
        }
        return Math.floor(delay / 60000);
    }

    if (delay !== null) {
        return Math.floor(delay / 60);
    }
    return null;
}

export function getDepartureDelay(
    post: TransportPost | ApiTransportPost,
): number | null {
    return calculateDelay(
        getDepartureTime(post.originStop),
        post?.manualDepartureTime,
        post?.originStop.departureDelay,
    );
}

export function getArrivalDelay(
    post: TransportPost | ApiTransportPost,
): number | null {
    return calculateDelay(
        getArrivalTime(post.destinationStop),
        post?.manualArrivalTime,
        post?.destinationStop.arrivalDelay,
    );
}

export function getArrivalTime(stop: Stop): DateTimeMaybeValid | null {
    const time = stop.arrivalTime || stop.departureTime || null;
    if (time) {
        return DateTime.fromISO(time);
    }

    return null;
}

export function getDepartureTime(stop: Stop): DateTimeMaybeValid | null {
    const time = stop.departureTime || stop.arrivalTime || null;
    if (time) {
        return DateTime.fromISO(time);
    }

    return null;
}
