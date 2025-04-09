/**
 * Methods to be called statically: getPosition()
 * Store position in localStorage and refresh if it has been more than 5 minutes
 */

export class LocationService {
    private static readonly REFRESH_INTERVAL = 5 * 60 * 1000; // 5 minutes

    public static async getPosition(): Promise<GeolocationPosition> {
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
}
