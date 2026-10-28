import { getServerUrl } from '@/Services/ServerConfig';
import { registerPlugin } from '@capacitor/core';
import { Preferences } from '@capacitor/preferences';
import axios from 'axios';

// Registered in the Android manifest and iOS Info.plist.
const CALLBACK_SCHEME = 'ch.reisetagebu';
const REDIRECT_URI = `${CALLBACK_SCHEME}://oauth/callback`;
// Native plugin: ios/App/App/MainViewController.swift (ASWebAuthenticationSession)
// and android/app/src/main/java/ch/reisetagebu/beta/WebAuthPlugin.kt (Custom Tabs).
const WebAuth = registerPlugin<{
    authenticate(options: {
        url: string;
        callbackScheme: string;
    }): Promise<{ url: string }>;
}>('WebAuth');

export interface NativeLoginResult {
    token: string;
    expiresAt: string;
}

interface ServerMetadata {
    authorization_endpoint: string;
    token_endpoint: string;
    registration_endpoint: string;
}

interface PendingLogin {
    state: string;
    verifier: string;
    clientId: string;
    tokenEndpoint: string;
    clientKey: string;
}

function base64url(bytes: Uint8Array): string {
    return btoa(String.fromCharCode(...bytes))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/, '');
}

function randomString(): string {
    return base64url(crypto.getRandomValues(new Uint8Array(32)));
}

/**
 * Returns this app's client_id on the given server, registering it via
 * Dynamic Client Registration (RFC 7591) the first time.
 */
async function getClientId(
    metadata: ServerMetadata,
    clientKey: string,
): Promise<string> {
    const { value } = await Preferences.get({ key: clientKey });
    if (value) {
        return value;
    }

    const { data } = await axios.post(metadata.registration_endpoint, {
        client_name: 'Reisetagebuch Companion App',
        client_uri: 'https://github.com/HerrLevin/reisetagebuch',
        logo_uri:
            'https://raw.githubusercontent.com/HerrLevin/reisetagebuch/main/public/assets/logo.webp',
        policy_uri: 'https://docs.reisetagebu.ch',
        tos_uri: 'https://docs.reisetagebu.ch',
        redirect_uris: [REDIRECT_URI],
        grant_types: ['authorization_code', 'refresh_token'],
        response_types: ['code'],
        token_endpoint_auth_method: 'none',
    });
    await Preferences.set({ key: clientKey, value: data.client_id });

    return data.client_id;
}

/**
 * Native only: runs the login in an in-app browser and exchanges the returned
 * code for a token. Resolves with null when the user cancels.
 */
export async function startNativeLogin(): Promise<NativeLoginResult | null> {
    const serverUrl = await getServerUrl();
    const { data: metadata } = await axios.get<ServerMetadata>(
        `${serverUrl}/.well-known/oauth-authorization-server`,
    );
    const clientKey = `oauthClient:${serverUrl}`;
    const clientId = await getClientId(metadata, clientKey);

    const pending: PendingLogin = {
        state: randomString(),
        verifier: randomString(),
        clientId,
        tokenEndpoint: metadata.token_endpoint,
        clientKey,
    };
    const challenge = base64url(
        new Uint8Array(
            await crypto.subtle.digest(
                'SHA-256',
                new TextEncoder().encode(pending.verifier),
            ),
        ),
    );
    const params = new URLSearchParams({
        client_id: clientId,
        redirect_uri: REDIRECT_URI,
        response_type: 'code',
        // Admin scopes are left out on purpose; admin tools are used in the browser.
        scope: 'read write',
        state: pending.state,
        code_challenge: challenge,
        code_challenge_method: 'S256',
    });

    const authorizeUrl = `${metadata.authorization_endpoint}?${params}`;

    let callbackUrl: string;
    try {
        ({ url: callbackUrl } = await WebAuth.authenticate({
            url: authorizeUrl,
            callbackScheme: CALLBACK_SCHEME,
        }));
    } catch (error) {
        if ((error as { code?: string }).code === 'CANCELLED') {
            return null;
        }
        throw error;
    }

    return finishLogin(callbackUrl, pending);
}

/**
 * Exchanges the code from the callback URL for an access token.
 */
async function finishLogin(
    url: string,
    pending: PendingLogin,
): Promise<NativeLoginResult> {
    const params = new URL(url).searchParams;

    const code = params.get('code');
    if (!code || params.get('state') !== pending.state) {
        throw new Error(`Invalid OAuth callback: ${url}`);
    }

    try {
        const { data } = await axios.post(
            pending.tokenEndpoint,
            new URLSearchParams({
                grant_type: 'authorization_code',
                client_id: pending.clientId,
                redirect_uri: REDIRECT_URI,
                code,
                code_verifier: pending.verifier,
            }),
        );

        return {
            token: data.access_token,
            expiresAt: new Date(
                Date.now() + data.expires_in * 1000,
            ).toISOString(),
        };
    } catch (error) {
        // The server forgot our registration (e.g. client deleted): register again next time.
        if (
            axios.isAxiosError(error) &&
            error.response?.data?.error === 'invalid_client'
        ) {
            await Preferences.remove({ key: pending.clientKey });
        }
        throw error;
    }
}
