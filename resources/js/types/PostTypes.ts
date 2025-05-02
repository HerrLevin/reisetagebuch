import { TransportMode } from '@/types/enums';
import { LocationEntry, PublicUser } from '@/types/index';

export type BasePost = {
    id: string;
    body: string;
    user: PublicUser;
    created_at: string;
    updated_at: string;
};

export type LocationPost = BasePost & {
    location: LocationEntry;
};

export type TransportPost = BasePost & {
    start: LocationEntry;
    stop: LocationEntry;
    start_time: string;
    stop_time: string;
    line: string;
    mode: TransportMode;
};
