describe('Timeline', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('renders the personal timeline', () => {
        cy.registerAndVisit('/home');

        cy.get('ul.list').should('exist');
    });

    it('shows public posts on the global timeline', () => {
        cy.apiRegister().then((auth) => {
            cy.apiCreateTextPost(auth.token, 'Global post', 'public').then(
                () => {
                    cy.visit('/home/global', {
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

                    cy.contains('Global post').should('be.visible');
                },
            );
        });
    });

    it('navigates between personal and global timeline', () => {
        cy.registerAndVisit('/home');

        // Click to navigate to global timeline
        cy.get('ul.list li').first().find('a').click();
        cy.url().should('include', '/home/global');

        // Click to navigate back to personal timeline
        cy.get('ul.list li').first().find('a').click();
        cy.url().should('include', '/home');
        cy.url().should('not.include', '/global');
    });
});
