import i18n from '@/i18n';
import { TransportMode } from '@/types/enums';

const { t } = i18n.global;

const TransportModeEmoji: Record<TransportMode, string> = {
    [TransportMode.TRANSIT]: '🚍',
    [TransportMode.WALK]: '🚶‍♂️',
    [TransportMode.BIKE]: '🚴‍♂️',
    [TransportMode.RENTAL]: '🚲',
    [TransportMode.CAR]: '🚗',
    [TransportMode.CAR_PARKING]: '🅿️',
    [TransportMode.ODM]: '🚌',
    [TransportMode.TRAM]: '🚋',
    [TransportMode.SUBWAY]: '🚇',
    [TransportMode.FERRY]: '⛴️',
    [TransportMode.AIRPLANE]: '✈️',
    [TransportMode.METRO]: '🚉',
    [TransportMode.BUS]: '🚌',
    [TransportMode.COACH]: '🚌',
    [TransportMode.RAIL]: '🚆',
    [TransportMode.HIGHSPEED_RAIL]: '🚄',
    [TransportMode.LONG_DISTANCE]: '🚅',
    [TransportMode.NIGHT_RAIL]: '🌙',
    [TransportMode.REGIONAL_FAST_RAIL]: '🚆',
    [TransportMode.REGIONAL_RAIL]: '🚆',
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
    metro: [TransportMode.METRO],
    tram: [TransportMode.TRAM],
    subway: [TransportMode.SUBWAY],
    bus: [TransportMode.BUS, TransportMode.COACH, TransportMode.ODM],
    ferry: [TransportMode.FERRY],
};

export function getColor(mode: TransportMode): string {
    switch (mode) {
        case TransportMode.TRANSIT:
            return '#FF9800'; // Orange
        case TransportMode.TRAM:
            return '#c72730'; // Deep Purple
        case TransportMode.METRO:
            return '#006f35';
        case TransportMode.SUBWAY:
            return '#003399';
        case TransportMode.FERRY:
            return '#40daff';
        case TransportMode.AIRPLANE:
            return '#009688';
        case TransportMode.COACH:
        case TransportMode.BUS:
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
