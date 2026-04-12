describe('Create Transport Post', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('creates a transport post with text and redirects to the post page', () => {
        cy.loginAndVisit('/posts/transport/departures');

        cy.get('[name="departure-search"]').type('Karlsruhe Hbf');
        cy.contains('Karlsruhe').click();
        cy.contains('Nightjet').click();
        cy.contains('Basel Bad Bf').click();
        cy.get('textarea.textarea').type('Hello from Cypress!');
        cy.get('button[type="submit"].btn-primary').click();

        cy.url().should('match', /\/posts\/[a-f0-9-]+$/);
        cy.contains('Hello from Cypress!').should('be.visible');
        cy.contains('Karlsruhe Hbf').should('be.visible');
        cy.contains('Basel Bad Bf').should('be.visible')
    });
});
