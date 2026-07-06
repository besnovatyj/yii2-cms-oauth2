/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Примеры использования OAuth2 API в TypeScript/JavaScript
 * Оба формата (JSON и form-urlencoded) работают одинаково просто!
 */

// ============================================
// ВАРИАНТ 1: JSON (как в старом filsh)
// ============================================

interface OAuth2TokenResponse {
    token_type: string;
    expires_in: number;
    access_token: string;
    refresh_token: string;
}

// С использованием fetch
async function getTokenJSON(username: string, password: string): Promise<OAuth2TokenResponse> {
    const response = await fetch('https://rest.yii2-cms.docker.localhost/oauth2/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            grant_type: 'password',
            client_id: 'test-client',
            client_secret: 'test-secret',
            username: username,
            password: password,
            scope: 'basic'
        })
    });

    if (!response.ok) {
        throw new Error(`OAuth2 error: ${response.statusText}`);
    }

    return await response.json();
}

// С использованием axios
import axios from 'axios';

async function getTokenJSONAxios(username: string, password: string): Promise<OAuth2TokenResponse> {
    const response = await axios.post<OAuth2TokenResponse>(
        'https://rest.yii2-cms.docker.localhost/oauth2/token',
        {
            grant_type: 'password',
            client_id: 'test-client',
            client_secret: 'test-secret',
            username: username,
            password: password,
            scope: 'basic'
        },
        {
            headers: {
                'Content-Type': 'application/json'
            }
        }
    );

    return response.data;
}

// ============================================
// ВАРИАНТ 2: Form-urlencoded (стандарт OAuth2)
// ============================================

// С использованием fetch
async function getTokenForm(username: string, password: string): Promise<OAuth2TokenResponse> {
    const params = new URLSearchParams({
        grant_type: 'password',
        client_id: 'test-client',
        client_secret: 'test-secret',
        username: username,
        password: password,
        scope: 'basic'
    });

    const response = await fetch('https://rest.yii2-cms.docker.localhost/oauth2/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
    });

    if (!response.ok) {
        throw new Error(`OAuth2 error: ${response.statusText}`);
    }

    return await response.json();
}

// С использованием axios (автоматически конвертирует в form-urlencoded!)
async function getTokenFormAxios(username: string, password: string): Promise<OAuth2TokenResponse> {
    const response = await axios.post<OAuth2TokenResponse>(
        'https://rest.yii2-cms.docker.localhost/oauth2/token',
        new URLSearchParams({
            grant_type: 'password',
            client_id: 'test-client',
            client_secret: 'test-secret',
            username: username,
            password: password,
            scope: 'basic'
        })
    );

    return response.data;
}

// ============================================
// Refresh Token
// ============================================

async function refreshToken(refreshToken: string): Promise<OAuth2TokenResponse> {
    // JSON вариант
    const response = await fetch('https://rest.yii2-cms.docker.localhost/oauth2/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            grant_type: 'refresh_token',
            client_id: 'test-client',
            client_secret: 'test-secret',
            refresh_token: refreshToken
        })
    });

    return await response.json();
}

// ============================================
// Использование access token
// ============================================

async function makeAuthenticatedRequest(accessToken: string) {
    const response = await fetch('https://rest.yii2-cms.docker.localhost/api/some-endpoint', {
        headers: {
            'Authorization': `Bearer ${accessToken}`
        }
    });

    return await response.json();
}

// ============================================
// Полный пример с автоматическим refresh
// ============================================

class OAuth2Client {
    private accessToken: string | null = null;
    private refreshToken: string | null = null;
    private expiresAt: number | null = null;

    constructor(
        private clientId: string,
        private clientSecret: string,
        private baseUrl: string
    ) {}

    async login(username: string, password: string): Promise<void> {
        const response = await fetch(`${this.baseUrl}/oauth2/token`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                grant_type: 'password',
                client_id: this.clientId,
                client_secret: this.clientSecret,
                username,
                password,
                scope: 'basic'
            })
        });

        const data: OAuth2TokenResponse = await response.json();
        this.setTokens(data);
    }

    private setTokens(data: OAuth2TokenResponse): void {
        this.accessToken = data.access_token;
        this.refreshToken = data.refresh_token;
        this.expiresAt = Date.now() + (data.expires_in * 1000) - 60000; // -1 минута для запаса
    }

    private async ensureValidToken(): Promise<void> {
        if (!this.accessToken || (this.expiresAt && Date.now() >= this.expiresAt)) {
            if (!this.refreshToken) {
                throw new Error('No refresh token available');
            }

            const response = await fetch(`${this.baseUrl}/oauth2/token`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    grant_type: 'refresh_token',
                    client_id: this.clientId,
                    client_secret: this.clientSecret,
                    refresh_token: this.refreshToken
                })
            });

            const data: OAuth2TokenResponse = await response.json();
            this.setTokens(data);
        }
    }

    async fetch(url: string, options: RequestInit = {}): Promise<Response> {
        await this.ensureValidToken();

        return fetch(url, {
            ...options,
            headers: {
                ...options.headers,
                'Authorization': `Bearer ${this.accessToken}`
            }
        });
    }
}

// Использование:
const client = new OAuth2Client(
    'test-client',
    'test-secret',
    'https://rest.yii2-cms.docker.localhost'
);

await client.login('root', '123');
const response = await client.fetch('https://rest.yii2-cms.docker.localhost/api/profile');
const profile = await response.json();
