import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'ch.reisetagebu.beta',
    appName: 'Reisetagebuch',
    webDir: 'dist-capacitor',
    plugins: {
        // Route fetch/XHR (and therefore axios) through native HTTP instead of
        // the WebView, so requests to a self-hosted instance are not subject
        // to browser CORS/mixed-content restrictions.
        CapacitorHttp: {
            enabled: true,
        },
    },
};

export default config;
