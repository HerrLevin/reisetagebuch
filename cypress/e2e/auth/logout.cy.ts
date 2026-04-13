describe('Logout', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('logs out and redirects to /login on phone', () => {
        cy.registerAndVisit('/home');

        // set viewport to mobile size
        cy.viewport('iphone-x');

        // Open the user dropdown menu in the navbar
        cy.get('.drawer-button').scrollIntoView().click();

        // Click the logout button in the dropdown
        cy.get('.drawer-side').should('be.visible');
        cy.get('.bg-base-200 > li > button').last().click();

        cy.url().should('include', '/login');
    });

    it('logs out and redirects to /login on PC', () => {
        cy.registerAndVisit('/home');

        cy.get('.card > .menu > li > button').first().click();

        cy.url().should('include', '/login');
    });
});
