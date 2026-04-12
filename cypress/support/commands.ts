/// <reference types="cypress" />

interface AuthResponse {
    token: string;
    user: Record<string, unknown>;
    expiresAt: string;
}

interface TestUser {
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
}

declare global {
    namespace Cypress {
        interface Chainable {
            apiRegister(user?: Partial<TestUser>): Chainable<AuthResponse>;
            apiLogin(email?: string, password?: string): Chainable<AuthResponse>;
            setAuthState(token: string, expiresAt: string): Chainable<void>;
            clearAuthState(): Chainable<void>;
            loginAndVisit(path: string, user?: Partial<TestUser>): Chainable<void>;
            apiCreateTextPost(
                token: string,
                body: string,
                visibility?: string,
            ): Chainable<Record<string, unknown>>;
            resetDatabase(): Chainable<void>;
        }
    }
}

const defaultUser: TestUser = {
    name: 'Test User',
    username: 'testuser',
    email: 'test@example.com',
    password: 'Password123!',
    password_confirmation: 'Password123!',
};

Cypress.Commands.add('apiRegister', (user?: Partial<TestUser>) => {
    const userData = { ...defaultUser, ...user };
    return cy
        .request({
            method: 'POST',
            url: '/api/auth/register',
            body: userData,
        })
        .then((response) => response.body as AuthResponse);
});

Cypress.Commands.add(
    'apiLogin',
    (email = defaultUser.email, password = defaultUser.password) => {
        return cy
            .request({
                method: 'POST',
                url: '/api/auth/login',
                body: { email, password },
            })
            .then((response) => response.body as AuthResponse);
    },
);

Cypress.Commands.add('setAuthState', (token: string, expiresAt: string) => {
    cy.window().then((win) => {
        win.localStorage.setItem(
            'auth',
            JSON.stringify({ token, expiresAt }),
        );
    });
});

Cypress.Commands.add('clearAuthState', () => {
    cy.window().then((win) => {
        win.localStorage.removeItem('auth');
        win.localStorage.removeItem('user');
    });
});

Cypress.Commands.add(
    'loginAndVisit',
    (path: string, user?: Partial<TestUser>) => {
        cy.visit('/register');

        const userData = { ...defaultUser, ...user };

        cy.get('#name').type(userData.name);
        cy.get('#username').type(userData.username);
        cy.get('#email').type(userData.email);
        cy.get('#password').type(userData.password);
        cy.get('#password_confirmation').type(userData.password_confirmation);
        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/home');

        // dismiss welcome modal if it appears
        cy.get('.btn-primary').first().click();

        cy.visit(path);
    },
);

Cypress.Commands.add(
    'apiCreateTextPost',
    (token: string, body: string, visibility = 'public') => {
        return cy
            .request({
                method: 'POST',
                url: '/api/posts/text',
                headers: {
                    Authorization: `Bearer ${token}`,
                },
                body: { body, visibility },
            })
            .then((response) => response.body as Record<string, unknown>);
    },
);

Cypress.Commands.add('resetDatabase', () => {
    cy.request({
        method: 'POST',
        url: '/api/cypress/reset',
        headers: {
            'X-Cypress-Token': Cypress.env('CYPRESS_TOKEN') || 'testing',
        },
    });
});

export const storeAuth = (win: Window, auth: AuthResponse) => {
    win.localStorage.setItem(
        'auth',
        JSON.stringify({
            token: auth.token,
            expiresAt: auth.expiresAt,
        }),
    );
    win.cookieStore.set('rtb_disallow_history', 'true').then(() => {
        // ignore
    });
};

export {};
