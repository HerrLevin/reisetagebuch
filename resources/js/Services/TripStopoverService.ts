import { DateTime } from 'luxon';
import { TransportPostStopoverDto } from '../../types/Api.gen';

function getRealTime(
    scheduledTime: string | null,
    delay: number | null,
    manualTime: string | null,
): DateTime | null {
    if (manualTime) {
        return DateTime.fromISO(manualTime);
    }
    if (!scheduledTime) {
        return null;
    }
    return DateTime.fromISO(scheduledTime).plus({ seconds: delay ?? 0 });
}

function getRealDeparture(stop: TransportPostStopoverDto): DateTime | null {
    return getRealTime(
        stop.scheduledDepartureTime,
        stop.departureDelay,
        stop.manualDepartureTime,
    );
}

export function getCurrentOrNextStopover(
    stopovers: TransportPostStopoverDto[],
    now: DateTime = DateTime.now(),
): TransportPostStopoverDto | null {
    if (stopovers.length === 0) {
        return null;
    }

    const sorted = [...stopovers].sort((a, b) => a.sequence - b.sequence);

    const pending = sorted.find((stop) => {
        const realDeparture = getRealDeparture(stop);
        return !realDeparture || realDeparture > now;
    });

    return pending ?? sorted.at(-1) ?? null;
}
