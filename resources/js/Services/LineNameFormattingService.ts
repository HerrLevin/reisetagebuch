import { StopTimeDto, TransportPost } from '../../types/Api.gen';

export function getLineName(
    displayName: string | null,
    routeShortName: string | null,
): string | null {
    let lineName = displayName || routeShortName;
    if (lineName) {
        // remove stuff in parentheses
        lineName = lineName.replaceAll(/\s*\(.*?\)\s*/g, '').trim();
        // add space between letters and numbers
        lineName = lineName.replaceAll(/([a-zA-Z])(?=\d)/g, '$1 ').trim();
        return lineName;
    }

    return null;
}

export function getDepartureLineName(StopTimeDto: StopTimeDto): string | null {
    return getLineName(StopTimeDto.displayName, StopTimeDto.routeShortName);
}

export function getPostLineName(post: TransportPost): string | null {
    return getLineName(post.trip.displayName, post.trip.tripShortName);
}

export function getTripNumber(
    displayName: string | null,
    routeShortName: string | null,
    tripShortName?: string | null,
): string | null {
    if (tripShortName) {
        const lineName = getLineName(displayName, routeShortName);
        if (lineName && lineName.trim() !== tripShortName.trim()) {
            return tripShortName;
        }
    }
    const tripName = displayName || routeShortName;
    // extract stuff in parentheses
    if (tripName) {
        const match = tripName.match(/\((.*?)\)/);
        if (match && match[1]) {
            return match[1].trim();
        }
    }

    return null;
}

export function getPostTripNumber(post: TransportPost): string | null {
    return getTripNumber(
        post.trip.displayName,
        post.trip.tripShortName,
        post.trip.tripShortName,
    );
}

export function getDepartureTripNumber(
    StopTimeDto: StopTimeDto,
): string | null {
    return getTripNumber(
        StopTimeDto.displayName,
        StopTimeDto.routeShortName,
        StopTimeDto.tripShortName,
    );
}
