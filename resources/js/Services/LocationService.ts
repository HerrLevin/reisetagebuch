/**
 * Methods to be called statically: getPosition()
 * Store position in localStorage and refresh if it has been more than 5 minutes
 */
import axios from 'axios';

export class LocationService {
    private static readonly REFRESH_INTERVAL = 30; // 30 Seconds

    public static async getPosition(
        isAuthenticated: boolean,
    ): Promise<GeolocationPosition> {
        if (!isAuthenticated) {
            throw new Error('User is not authenticated.');
        }
        const localStoragePosition = this.getFromLocalStorage();
        if (localStoragePosition) {
            return localStoragePosition;
        }

        const position = await this.getCurrentPosition();
        this.saveToLocalStorage(position);
        return position;
    }

    private static getFromLocalStorage(): GeolocationPosition | null {
        const position = localStorage.getItem('position');
        const maxTime = Date.now() - this.REFRESH_INTERVAL;

        if (position) {
            const parsedPosition: GeolocationPosition = JSON.parse(position);
            if (parsedPosition.timestamp > maxTime) {
                return parsedPosition;
            } else {
                localStorage.removeItem('position');
                localStorage.removeItem('lastRefresh');
            }
        }
        return null;
    }

    private static saveToLocalStorage(position: GeolocationPosition): void {
        localStorage.setItem('position', JSON.stringify(position));
    }

    public static async getCurrentPosition(): Promise<GeolocationPosition> {
        if (!navigator.geolocation) {
            throw new Error('Geolocation is not supported by this browser.');
        }

        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => resolve(position),
                (error) => reject(error),
            );
        });
    }

    public static prefetchLocationData(position: GeolocationPosition): void {
        axios
            .get(
                route('posts.create.prefetch', {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                }),
            )
            .then(() => {})
            .catch(() => {});
    }
}
