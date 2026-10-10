import { api } from '@/api';
import { StopoverPopupInfo } from '@/Components/Map.vue';
import { GeometryCollection, MultiPoint } from 'geojson';
import { StopPlaceDto, TransportPost } from '../../types/Api.gen';

export interface FederatedTripGeometry {
    lineString: GeometryCollection;
    stopovers: MultiPoint;
    stopoverDetails: StopoverPopupInfo[];
}

function diffSeconds(
    scheduled: string | null,
    actual: string | null,
): number | null {
    if (!scheduled || !actual) {
        return null;
    }
    return Math.round(
        (new Date(actual).getTime() - new Date(scheduled).getTime()) / 1000,
    );
}

export function buildStopoverPopupInfo(stop: StopPlaceDto): StopoverPopupInfo {
    return {
        name: stop.name,
        longitude: stop.longitude,
        latitude: stop.latitude,
        scheduledArrivalTime: stop.scheduledArrival,
        scheduledDepartureTime: stop.scheduledDeparture,
        arrivalDelay: diffSeconds(stop.scheduledArrival, stop.arrival),
        departureDelay: diffSeconds(stop.scheduledDeparture, stop.departure),
        manualArrivalTime: null,
        manualDepartureTime: null,
    };
}

function findStopIndex(
    stops: StopPlaceDto[],
    target: { latitude: number; longitude: number },
    fromIndex = 0,
): number {
    const epsilon = 0.001;
    for (let i = fromIndex; i < stops.length; i++) {
        if (
            Math.abs(stops[i].latitude - target.latitude) < epsilon &&
            Math.abs(stops[i].longitude - target.longitude) < epsilon
        ) {
            return i;
        }
    }
    return -1;
}

export async function loadFederatedTripGeometry(
    tPost: TransportPost,
): Promise<FederatedTripGeometry | null> {
    const foreignId = tPost.trip.foreignId;
    if (!foreignId) {
        return null;
    }

    try {
        const response = await api.locations.stopovers({
            tripId: foreignId,
            startId: tPost.originStop.id,
            startTime:
                tPost.originStop.departureTime ??
                tPost.originStop.arrivalTime ??
                tPost.destinationStop.arrivalTime ??
                new Date().toISOString(),
        });

        const leg = response.data.trip?.legs?.[0];
        if (!leg) {
            return null;
        }

        const allStops: StopPlaceDto[] = [
            leg.from,
            ...leg.intermediateStops,
            leg.to,
        ];

        const originIndex = findStopIndex(allStops, tPost.originStop.location);
        const destinationIndex =
            originIndex === -1
                ? -1
                : findStopIndex(
                      allStops,
                      tPost.destinationStop.location,
                      originIndex,
                  );

        if (
            originIndex === -1 ||
            destinationIndex === -1 ||
            destinationIndex <= originIndex
        ) {
            return null;
        }

        const relevantStops = allStops.slice(originIndex, destinationIndex + 1);
        const coordinates = relevantStops.map(
            (stop) => [stop.longitude, stop.latitude] as [number, number],
        );

        const lineString = await getLineString(relevantStops);

        return {
            lineString:
                lineString ??
                ({
                    type: 'LineString',
                    coordinates,
                } as unknown as GeometryCollection),
            stopovers: {
                type: 'MultiPoint',
                coordinates,
            } as unknown as MultiPoint,
            stopoverDetails: relevantStops
                .slice(1, -1)
                .map(buildStopoverPopupInfo),
        };
    } catch {
        // Trip fell out of the schedule window, or this instance's
        // transitous backend doesn't know this foreignId.
        return null;
    }
}

async function getLineString(relevantStops: StopPlaceDto[]) {
    const fromStopId = relevantStops.at(0)?.tripStopId;
    const toStopId = relevantStops.at(-1)?.tripStopId;

    if (!fromStopId || !toStopId) {
        return null;
    }

    let lineString: GeometryCollection | null = null;

    await api.map
        .getLineStringBetween({
            from: fromStopId,
            to: toStopId,
        })
        .then((response) => {
            lineString = response.data as GeometryCollection;
        })
        .catch(() => {
            lineString = null;
        });

    return lineString;
}
