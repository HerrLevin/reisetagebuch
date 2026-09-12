/**
 * Methods to be called statically: getPosition()
 * Store position in localStorage and refresh if it has been more than 5 minutes
 */
import { api } from '@/api';
import { Geolocation, Position } from '@capacitor/geolocation';

export class LocationService {
    private static readonly REFRESH_INTERVAL = 30; // 30 Seconds

    public static async getPosition(
        isAuthenticated: boolean,
    ): Promise<Position> {
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

    private static getFromLocalStorage(): Position | null {
        const position = localStorage.getItem('position');
        const maxTime = Date.now() - this.REFRESH_INTERVAL;

        if (position) {
            const parsedPosition: Position = JSON.parse(position);
            if (parsedPosition.timestamp > maxTime) {
                return parsedPosition;
            } else {
                localStorage.removeItem('position');
            }
        }
        return null;
    }

    private static saveToLocalStorage(position: Position): void {
        localStorage.setItem('position', JSON.stringify(position));
    }

    // Uses the Capacitor Geolocation plugin rather than the bare
    // navigator.geolocation Web API: WKWebView does not implement the
    // Geolocation Web API on its own, so this is required for the app to
    // get a location at all when running natively (see Info.plist /
    // AndroidManifest.xml for the corresponding permission declarations).
    public static async getCurrentPosition(): Promise<Position> {
        return Geolocation.getCurrentPosition();
    }

    public static prefetchLocationData(position: Position): void {
        api.location
            .prefetchLocation({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            })
            .then(() => {
                // do nothing, we just want to cache the data
            });
    }
}
