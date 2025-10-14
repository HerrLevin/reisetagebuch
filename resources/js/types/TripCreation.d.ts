import { TransportMode } from '@/types/enums';
import { LucideProps } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import * as vue from 'vue';

export type CreateTripForm = {
    startLocation: TripLocation | null;
    endLocation: TripLocation | null;
    departureTime: DateTime | null;
    arrivalTime: DateTime | null;
    transportMode: TransportMode | null;
    lineName: string | null;
    tripShortName: string | null;
    stops: TripLocation[];
};

export type TripLocation = {
    order?: number;
    name: string;
    identifier: string;
    id?: string | null;
    latitude: number;
    longitude: number;
};

export type FormStops = {
    order: number;
    identifier: string;
};

export type Provider = {
    name: string;
    // eslint-disable-next-line
    icon: vue.FunctionalComponent<LucideProps, {}, any, {}>;
};

export type ProviderKey = 'transitous' | 'airports';
export type Providers = Record<ProviderKey, Provider>;
