describe('Login', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('displays the login form', () => {
        cy.visit('/login');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
        cy.get('button[type="submit"]').should('be.visible');
    });

    it('logs in successfully and redirects to /home', () => {
        cy.apiRegister();
        cy.visit('/login');

        cy.get('#email').type('test@example.com');
        cy.get('#password').type('password');
        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/home');
    });

    it('shows error for invalid credentials', () => {
        cy.visit('/login');

        cy.get('#email').type('wrong@example.com');
        cy.get('#password').type('Wrongpassword');
        cy.get('button[type="submit"]').click();

        cy.get('.text-error').should('be.visible');
    });

    it('redirects authenticated users away from login', () => {
        cy.apiRegister().then((auth) => {
            cy.visit('/login', {
                onBeforeLoad(win) {
                    win.localStorage.setItem(
                        'auth',
                        JSON.stringify({
                            token: auth.token,
                            expiresAt: auth.expiresAt,
                        }),
                    );
                },
            });
        });

        cy.url().should('include', '/home');
    });

    it('redirects unauthenticated users to /login', () => {
        cy.visit('/home');
        cy.url().should('include', '/login');
    });
});
