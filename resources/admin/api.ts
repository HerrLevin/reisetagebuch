import { Api } from '../types/Api.gen';

// Cookie auth only: the /admin page sets Passport's laravel_token cookie and
// axios sends the XSRF header itself, so no bearer token is ever attached.
const api = new Api({ baseURL: '/api' });

api.instance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        return Promise.reject(error);
    },
);

export { api };
