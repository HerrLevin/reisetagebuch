import { BasePost, LocationPost, TransportPost } from '../../types/Api.gen';

export const isLocationPost = (
    post: BasePost | LocationPost | TransportPost | null,
): post is LocationPost => {
    return post !== null && (post as LocationPost).location !== undefined;
};

export const isTransportPost = (
    post: BasePost | null,
): post is TransportPost => {
    return post !== null && (post as TransportPost).originStop !== undefined;
};
