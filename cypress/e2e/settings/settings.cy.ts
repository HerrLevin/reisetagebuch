describe('Settings', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('displays the settings form', () => {
        cy.registerAndVisit('/settings');

        cy.get('#name').should('be.visible');
        cy.get('#username').should('be.visible');
        cy.get('#email').should('be.visible');
    });

    it('updates the display name', () => {
        cy.registerAndVisit('/settings');

        cy.get('#name').clear().type('Updated Name');
        cy.get('button.btn-primary').first().click();

        // Verify the success message appears
        cy.contains(/saved/i).should('be.visible');
    });

    it('requires authentication', () => {
        cy.visit('/settings');
        cy.url().should('include', '/login');
    });
});
