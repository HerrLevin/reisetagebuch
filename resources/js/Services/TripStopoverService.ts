import { DateTime } from 'luxon';
import { TransportPostStopoverDto } from '../../types/Api.gen';

// arrivalDelay/departureDelay are in seconds and are added to the scheduled
// time to get the real (predicted) time; a manual time is already real and
// is used as-is.
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

// A stopover is "current" while the traveler is there (arrived but not yet
// departed) and becomes "next" once no earlier stopover still qualifies.
// Both cases are simply the first stopover, in sequence order, whose real
// departure hasn't happened yet. If every stopover has already been
// departed from, the last one is returned.
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
