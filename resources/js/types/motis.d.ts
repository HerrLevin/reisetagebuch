import { LocationIdentifier } from '@/types/index';

export type Area = {
    name: string;
    adminLevel: number;
    matched: boolean;
    unique?: boolean;
    default?: boolean;
};

export enum AreaType {
    ADDRESS = 'ADDRESS',
    PLACE = 'PLACE',
    STOP = 'STOP',
}

export type AutocompleteResponse = {
    type: AreaType;
    tokens: Array<Array<number, number>>;
    name: string;
    identifier: string;
    id?: string | null;
    lat: number;
    lon: number;
    level?: number;
    street?: string;
    houseNumber?: string;
    country?: string;
    zip?: string;
    tz?: string;
    areas?: Area[];
    score: number;
    identifiers: LocationIdentifier[];
};
