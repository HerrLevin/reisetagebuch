import { Api } from '../types/Api.gen';

const apiBaseUrl = '/api';
const api = new Api({ baseURL: apiBaseUrl });

export function setupApiAuth(token: string | null) {
    if (token) {
        api.instance.defaults.headers.common['Authorization'] =
            `Bearer ${token}`;
    } else {
        delete api.instance.defaults.headers.common['Authorization'];
    }
}

export { api };
