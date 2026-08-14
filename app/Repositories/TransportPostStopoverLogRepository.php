<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TransportPost;
use App\Models\TransportPostStopoverLog;
use App\Models\TransportTripStop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TransportPostStopoverLogRepository
{
    /**
     * @return Collection<int, TransportPostStopoverLog>
     */
    public function getLogsForTransportPost(string $transportPostId): Collection
    {
        return TransportPostStopoverLog::where('transport_post_id', $transportPostId)->get();
    }

    public function logArrival(TransportPost $transportPost, TransportTripStop $stop, Carbon $timestamp): TransportPostStopoverLog
    {
        return $this->updateOrCreate($transportPost, $stop, ['manual_arrival' => $timestamp]);
    }

    public function logDeparture(TransportPost $transportPost, TransportTripStop $stop, Carbon $timestamp): TransportPostStopoverLog
    {
        return $this->updateOrCreate($transportPost, $stop, ['manual_departure' => $timestamp]);
    }

    public function clearArrival(TransportPost $transportPost, TransportTripStop $stop): void
    {
        $this->clear($transportPost, $stop, 'manual_arrival');
    }

    public function clearDeparture(TransportPost $transportPost, TransportTripStop $stop): void
    {
        $this->clear($transportPost, $stop, 'manual_departure');
    }

    private function clear(TransportPost $transportPost, TransportTripStop $stop, string $column): void
    {
        $log = TransportPostStopoverLog::where('transport_post_id', $transportPost->id)
            ->where('transport_trip_stop_id', $stop->id)
            ->first();

        if ($log === null) {
            return;
        }

        $otherColumn = $column === 'manual_arrival' ? 'manual_departure' : 'manual_arrival';
        if ($log->{$otherColumn} === null) {
            $log->delete();

            return;
        }

        $log->{$column} = null;
        $log->save();
    }

    private function updateOrCreate(TransportPost $transportPost, TransportTripStop $stop, array $values): TransportPostStopoverLog
    {
        return TransportPostStopoverLog::updateOrCreate(
            [
                'transport_post_id' => $transportPost->id,
                'transport_trip_stop_id' => $stop->id,
            ],
            $values
        );
    }
}
