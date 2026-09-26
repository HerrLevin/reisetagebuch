import { App } from '@capacitor/app';
import { Capacitor } from '@capacitor/core';

/**
 * Android has a hardware/gesture back button that, unlike iOS, is not wired
 * up to WebView navigation by Capacitor automatically. Without this, the
 * button minimizes the app instead of navigating back through the SPA's
 * route history.
 */
export function initBackButtonHandler(): void {
    if (Capacitor.getPlatform() !== 'android') {
        return;
    }

    App.addListener('backButton', ({ canGoBack }) => {
        if (canGoBack) {
            window.history.back();
        } else {
            App.exitApp();
        }
    });
}
