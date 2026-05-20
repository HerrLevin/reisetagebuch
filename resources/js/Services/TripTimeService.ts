import { DateTime, DateTimeMaybeValid } from 'luxon';
import { StopDto, TransportPost } from '../../types/Api.gen';

// delay in minutes
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

export function getDepartureDelay(post: TransportPost): number | null {
    return calculateDelay(
        getDepartureTime(post.originStop),
        post?.manualDepartureTime,
        post?.originStop.departureDelay,
    );
}

export function getArrivalDelay(post: TransportPost): number | null {
    return calculateDelay(
        getArrivalTime(post.destinationStop),
        post?.manualArrivalTime,
        post?.destinationStop.arrivalDelay,
    );
}

export function getRealDepartureTime(stop: StopDto): DateTimeMaybeValid | null {
    const scheduledTime = getDepartureTime(stop);
    const delay = calculateDelay(scheduledTime, null, stop.departureDelay);

    const realTime = scheduledTime?.plus({ minutes: delay ?? 0 });

    if (realTime?.isValid) {
        return realTime;
    }

    return null;
}

export function getRealArrivalTime(stop: StopDto): DateTimeMaybeValid | null {
    const scheduledTime = getArrivalTime(stop);
    const delay = calculateDelay(scheduledTime, null, stop.arrivalDelay);

    const realTime = scheduledTime?.plus({ minutes: delay ?? 0 });

    if (realTime?.isValid) {
        return realTime;
    }

    return null;
}

export function getArrivalTime(stop: StopDto): DateTimeMaybeValid | null {
    const time = stop.arrivalTime || stop.departureTime || null;
    if (time) {
        return DateTime.fromISO(time);
    }

    return null;
}

export function getDepartureTime(stop: StopDto): DateTimeMaybeValid | null {
    const time = stop.departureTime || stop.arrivalTime || null;
    if (time) {
        return DateTime.fromISO(time);
    }

    return null;
}
