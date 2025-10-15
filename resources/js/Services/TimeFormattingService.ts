import { getArrivalTime, getDepartureTime } from '@/Services/TripTimeService';
import { Stop } from '@/types/PostTypes';
import { DateTime, DateTimeMaybeValid } from 'luxon';

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
    stop: Stop,
    manualTime: string | null = null,
    departureDelay: number | null = null,
): string | null {
    const departureTime = getDepartureTime(stop);
    return formatStopTime(departureTime, manualTime, departureDelay);
}

export function formatArrivalTime(
    stop: Stop,
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
