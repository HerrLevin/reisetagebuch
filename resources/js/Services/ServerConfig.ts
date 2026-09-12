import { Capacitor } from '@capacitor/core';
import { Preferences } from '@capacitor/preferences';

const STORAGE_KEY = 'serverUrl';

export function isNative(): boolean {
    return Capacitor.isNativePlatform();
}

function normalizeUrl(url: string): string {
    let normalized = url.trim().replace(/\/+$/, '');

    if (!/^https?:\/\//i.test(normalized)) {
        normalized = `https://${normalized}`;
    }

    return normalized;
}

export async function getServerUrl(): Promise<string> {
    if (!isNative()) {
        return '';
    }

    const { value } = await Preferences.get({ key: STORAGE_KEY });
    return value ?? '';
}

export async function setServerUrl(url: string): Promise<string> {
    const normalized = normalizeUrl(url);
    await Preferences.set({ key: STORAGE_KEY, value: normalized });
    return normalized;
}

export async function clearServerUrl(): Promise<void> {
    await Preferences.remove({ key: STORAGE_KEY });
}

export async function getServerOrigin(): Promise<string> {
    if (!isNative()) {
        return window.location.origin;
    }

    return getServerUrl();
}
