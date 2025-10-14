<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Jobs\RerouteStops;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Repositories\LocationRepository;
use App\Repositories\TransportTripRepository;
use App\Services\TransitousRequestService;
use Carbon\Carbon;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class TripController extends Controller
{
    private TransportTripRepository $transportTripRepository;

    private LocationRepository $locationRepository;

    private TransitousRequestService $transitousRequestService;

    public function __construct(
        TransportTripRepository $transportTripRepository,
        LocationRepository $locationRepository,
        TransitousRequestService $transitousRequestService
    ) {
        $this->transportTripRepository = $transportTripRepository;
        $this->locationRepository = $locationRepository;
        $this->transitousRequestService = $transitousRequestService;
    }

    /**
     * @throws RecordNotFoundException
     */
    private function getOrCreateAndAddStopToTrip(TransportTrip $trip, array $stopover, int $key): TransportTripStop
    {
        if ($stopover['identifierType'] == 'id') {
            $origin = $this->locationRepository->getLocationById($stopover['identifier']);
            if (! $origin) {
                throw new RecordNotFoundException('Location not found');
            }
        } else {
            $origin = $this->locationRepository->getLocationByIdentifier($stopover['identifier'], 'node', 'motis');
        }
        if (! $origin) {
            $location = $this->transitousRequestService->getLocationByIdentifier($stopover['identifier']);
            if (! $location) {
                throw new RecordNotFoundException('Origin location not found');
            }
            $origin = $this->locationRepository->getOrCreateLocationByIdentifier(
                $location->name,
                $location->latitude,
                $location->longitude,
                $location->stopId,
                'stop',
                'motis'
            );
        }

        return $this->transportTripRepository->addStopToTrip(
            $trip,
            $origin,
            $stopover['order'],
            ! empty($stopover['arrivalTime']) ? Carbon::parse($stopover['arrivalTime'])->setTimezone('UTC') : null,
            ! empty($stopover['departureTime']) ? Carbon::parse($stopover['departureTime'])->setTimezone('UTC') : null
        );
    }

    /**
     * @throws Throwable
     */
    public function store(StoreTripRequest $request): TransportTrip
    {
        try {
            DB::beginTransaction();
            // create random identifier
            $identifier = 'rtb_trip-'.bin2hex(random_bytes(16));

            $stopovers = $request->input('stops', []);

            // add origin at the start
            array_unshift($stopovers, [
                'identifier' => $request->origin,
                'identifierType' => $request->originType,
                'arrivalTime' => null,
                'departureTime' => $request->departureTime,
                'order' => 0,
            ]);

            // add destination at the end
            $stopovers[] = [
                'identifier' => $request->destination,
                'identifierType' => $request->destinationType,
                'arrivalTime' => $request->arrivalTime,
                'departureTime' => null,
                'order' => count($stopovers) + 1,
            ];

            $trip = $this->transportTripRepository->getOrCreateTrip(
                mode: $request->mode,
                foreignId: $identifier,
                provider: 'road-to-better',
                lineName: $request->lineName,
                routeLongName: $request->routeLongName,
                tripShortName: $request->tripShortName,
                displayName: $request->displayName,
                user: auth()->user()
            );

            $stops = [];
            foreach ($stopovers as $key => $stopover) {
                $stops[] = $this->getOrCreateAndAddStopToTrip($trip, $stopover, $key);
            }

            return $trip;
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        } finally {
            DB::commit();

            RerouteStops::dispatch($trip, $stops);
        }
    }
}
