import i18n from '@/i18n';
import { contrastingForeground } from '@/Services/ColorHelper';
import {
    LegDto,
    TransportMode,
    TransportPost,
    TripDto,
} from '../../types/Api.gen';

const { t } = i18n.global;

const TransportModeEmoji: Record<TransportMode, string> = {
    [TransportMode.TRANSIT]: '🚍',
    [TransportMode.WALK]: '🚶‍♂️',
    [TransportMode.BIKE]: '🚴‍♂️',
    [TransportMode.RENTAL]: '🚲',
    [TransportMode.CAR]: '🚗',
    [TransportMode.CAR_PARKING]: '🅿️',
    [TransportMode.CAR_DROPOFF]: '🚗',
    [TransportMode.ODM]: '🚌',
    [TransportMode.FLEX]: '🚍',
    [TransportMode.TRAM]: '🚊',
    [TransportMode.SUBWAY]: '🚇',
    [TransportMode.FERRY]: '⛴️',
    [TransportMode.AIRPLANE]: '✈️',
    [TransportMode.METRO]: '🚉',
    [TransportMode.SUBURBAN]: '🚉',
    [TransportMode.BUS]: '🚌',
    [TransportMode.COACH]: '🚌',
    [TransportMode.RAIL]: '🚆',
    [TransportMode.HIGHSPEED_RAIL]: '🚄',
    [TransportMode.LONG_DISTANCE]: '🚅',
    [TransportMode.NIGHT_RAIL]: '🌙',
    [TransportMode.REGIONAL_FAST_RAIL]: '🚆',
    [TransportMode.REGIONAL_RAIL]: '🚆',
    [TransportMode.CABLE_CAR]: '🚠',
    [TransportMode.FUNICULAR]: '🚞',
    [TransportMode.AERIAL_LIFT]: '🚡',
    [TransportMode.AREAL_LIFT]: '↕️',
    [TransportMode.OTHER]: '🫥',
};

export function getName(mode: TransportMode): string {
    return t(`transport_modes.${mode}`);
}

export function getEmoji(mode: TransportMode): string {
    return TransportModeEmoji[mode] ?? '🚏';
}

export const FilterGroups: Record<string, TransportMode[]> = {
    long_distance: [
        TransportMode.HIGHSPEED_RAIL,
        TransportMode.LONG_DISTANCE,
        TransportMode.NIGHT_RAIL,
    ],
    regional: [TransportMode.REGIONAL_FAST_RAIL, TransportMode.REGIONAL_RAIL],
    metro: [TransportMode.METRO, TransportMode.SUBURBAN],
    tram: [
        TransportMode.TRAM,
        TransportMode.CABLE_CAR,
        TransportMode.FUNICULAR,
        TransportMode.AERIAL_LIFT,
        TransportMode.AREAL_LIFT,
    ],
    subway: [TransportMode.SUBWAY],
    bus: [
        TransportMode.BUS,
        TransportMode.COACH,
        TransportMode.ODM,
        TransportMode.FLEX,
    ],
    ferry: [TransportMode.FERRY],
};

export function getColorForPost(post: TransportPost): string {
    if (
        post.trip.routeColor !== null &&
        (post.trip.routeColor.length == 3 || post.trip.routeColor.length == 6)
    ) {
        return `#${post.trip.routeColor}`;
    }
    return getColor(post.trip.mode);
}

export function getRouteBadgeStyle(trip: TripDto | LegDto) {
    const background = getRouteColor(trip);
    const foreground = contrastingForeground(
        background,
        getRouteTextColor(trip),
    );

    return `background-color: ${background}; color: ${foreground}`;
}

export function getRouteTextColor(trip: TripDto | LegDto) {
    if (trip.routeTextColor && trip.routeTextColor.length > 2) {
        return '#' + trip.routeTextColor;
    }

    return '#FFFFFF';
}

export function getRouteColor(trip: TripDto | LegDto) {
    if (trip.routeColor && trip.routeColor.length > 2) {
        return '#' + trip.routeColor;
    }

    return getColor(trip.mode);
}

export function getColor(mode: TransportMode): string {
    switch (mode) {
        case TransportMode.TRANSIT:
            return '#FF9800'; // Orange
        case TransportMode.TRAM:
        case TransportMode.CABLE_CAR:
        case TransportMode.FUNICULAR:
        case TransportMode.AERIAL_LIFT:
        case TransportMode.AREAL_LIFT:
            return '#c72730'; // Deep Purple
        case TransportMode.METRO:
        case TransportMode.SUBURBAN:
            return '#006f35';
        case TransportMode.SUBWAY:
            return '#003399';
        case TransportMode.FERRY:
            return '#40daff';
        case TransportMode.AIRPLANE:
            return '#009688';
        case TransportMode.COACH:
        case TransportMode.BUS:
        case TransportMode.ODM:
            return '#a3017b';
        case TransportMode.NIGHT_RAIL:
            return '#15243e';
        case TransportMode.REGIONAL_FAST_RAIL:
        case TransportMode.REGIONAL_RAIL:
            return '#FF5722';
        default:
            return '#1e1919';
    }
}
