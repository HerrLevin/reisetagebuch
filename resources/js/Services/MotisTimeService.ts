import { StopPlace } from '@/types';
import { DateTime } from 'luxon';

class StopTime {
    private readonly stop: StopPlace;
    private _scheduledTime: DateTime | null = null;
    private _actualTime: DateTime | null = null;

    constructor(stop: StopPlace) {
        this.stop = stop;
    }

    private get scheduledTime(): DateTime | null {
        if (this._scheduledTime) {
            return this._scheduledTime;
        }

        const _scheduledTime =
            this.stop.scheduledDeparture ?? this.stop.scheduledArrival;

        this._scheduledTime = _scheduledTime
            ? DateTime.fromISO(_scheduledTime)
            : null;
        return this._scheduledTime;
    }

    private get actualTime(): DateTime | null {
        if (this._actualTime) {
            return this._actualTime;
        }

        const _time = this.stop.departure ?? this.stop.arrival;
        this._actualTime = _time ? DateTime.fromISO(_time) : null;

        return this._actualTime;
    }

    get delay(): number {
        const luxonTime = this.actualTime;
        const luxonSchedule = this.actualTime;
        return luxonTime && luxonSchedule
            ? luxonTime.diff(luxonSchedule, 'minutes').minutes
            : -1;
    }

    get timeString(): string {
        const luxonTime = this.actualTime ?? this.scheduledTime;
        return luxonTime ? this.formatTime(luxonTime) : '';
    }

    get plannedTimeString(): string {
        const luxonSchedule = this.scheduledTime;
        return luxonSchedule ? this.formatTime(luxonSchedule) : '';
    }

    get plannedTime(): DateTime | null {
        return this.scheduledTime;
    }

    private formatTime(date: DateTime): string {
        return date.toFormat('HH:mm');
    }
}

export default StopTime;
