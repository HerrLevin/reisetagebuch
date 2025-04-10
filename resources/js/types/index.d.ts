import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};

export type LocationEntry = {
    id: string;
    name: string;
    latitude: number;
    longitude: number;
    distance: ?number;
    tags: LocationTag[];
};

export type LocationTag = {
    key: string;
    value: string;
};
