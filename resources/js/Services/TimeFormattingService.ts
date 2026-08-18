import {
    getArrivalDelay,
    getArrivalTime,
    getDepartureDelay,
    getDepartureTime,
} from '@/Services/TripTimeService';
import { isTransportPost } from '@/types/PostTypes';
import { DateTime, DateTimeMaybeValid } from 'luxon';
import {
    BasePost,
    LocationPost,
    StopDto,
    TransportPost,
} from '../../types/Api.gen';

export function formatDelayAbsoluteAmount(minutes: number): string {
    if (minutes < 60) {
        return `${minutes} m`;
    } else if (minutes < 1440) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        return mins === 0 ? `${hours} h` : `${hours} h ${mins} m`;
    } else {
        const days = Math.floor(minutes / 1440);
        const hours = Math.floor((minutes % 1440) / 60);
        return hours === 0 ? `${days} d` : `${days} d ${hours} h`;
    }
}

export function formatDelay(minutes: number): string {
    if (minutes < 0) {
        return `-${formatDelayAbsoluteAmount(-minutes)}`;
    } else {
        return `+${formatDelayAbsoluteAmount(minutes)}`;
    }
}

function formatTime(dateTime: DateTime): string {
    return dateTime.toLocaleString({
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function formatDepartureTime(
    stop: StopDto,
    manualTime: string | null = null,
    departureDelay: number | null = null,
): string | null {
    const departureTime = getDepartureTime(stop);
    return formatStopTime(departureTime, manualTime, departureDelay);
}

export function formatArrivalTime(
    stop: StopDto,
    manualTime: string | null = null,
    arrivalDelay: number | null = null,
): string | null {
    const arrivalTime = getArrivalTime(stop);
    return formatStopTime(arrivalTime, manualTime, arrivalDelay);
}

function formatStopTime(
    time: DateTimeMaybeValid | null,
    manualTime: string | null = null,
    delay: number | null = null,
): string | null {
    if (manualTime && manualTime.length > 0) {
        const date = DateTime.fromISO(manualTime);
        return formatTime(date);
    }
    if (time) {
        if (delay) {
            const adjustedDate = time.plus({ minutes: delay });
            return formatTime(adjustedDate);
        }
        return formatTime(time);
    }

    return null;
}

export function getTransportProgress(
    post: BasePost | TransportPost | LocationPost | null,
    now: number = Date.now(),
) {
    if (!isTransportPost(post)) return 0;

    const transportPost = post as TransportPost;
    const departureDelay = getDepartureDelay(transportPost) || 0;
    const departureTime = getDepartureTime(transportPost.originStop)
        ?.plus({ minutes: departureDelay })
        .toISO();
    const arrivalDelay = getArrivalDelay(transportPost) || 0;
    const arrivalTime = getArrivalTime(transportPost.destinationStop)
        ?.plus({ minutes: arrivalDelay })
        .toISO();

    if (!departureTime || !arrivalTime) return 0;

    const departure = new Date(departureTime).getTime();
    const arrival = new Date(arrivalTime).getTime();

    if (now < departure) return 0;
    if (now > arrival) return 100;

    const totalDuration = arrival - departure;
    const elapsed = now - departure;

    return Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
}
