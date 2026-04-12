describe('Create Text Post', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('creates a text post and redirects to the post page', () => {
        cy.loginAndVisit('/posts/new');

        cy.get('textarea.textarea').type('Hello from Cypress!');
        cy.get('button[type="submit"].btn-primary').click();

        cy.url().should('match', /\/posts\/[a-f0-9-]+$/);
        cy.contains('Hello from Cypress!').should('be.visible');
    });

    it('requires authentication to create a post', () => {
        cy.visit('/posts/new');
        cy.url().should('include', '/login');
    });
});
