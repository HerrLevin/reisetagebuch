describe('Create Transport Post', () => {
    before(() => {
        cy.seedDatabase();
        cy.login();
    });

    beforeEach(() => {
        cy.login();
    });

    it('creates a transport post with private visibility', () => {
        checkIntoNightjet();

        cy.contains('Public').click();
        cy.contains('Private').click();
        cy.get('button[type="submit"].btn-primary').click();

        cy.url().should('match', /\/posts\/[a-f0-9-]+$/);
        cy.location('pathname').then((pathname) => {
            cy.contains('Karlsruhe Hbf').should('be.visible');
            cy.contains('Basel Bad Bf').should('be.visible');
            cy.logout();
            cy.visit(pathname);
            cy.contains('403');
        });
    });

    it('creates a transport post without text and redirects to the post page', () => {
        checkIntoNightjet();

        cy.get('button[type="submit"].btn-primary').click();

        cy.url().should('match', /\/posts\/[a-f0-9-]+$/);
        cy.should('not.contain', 'Hello from Cypress!');
        cy.contains('Karlsruhe Hbf').should('be.visible');
        cy.contains('Basel Bad Bf').should('be.visible');
    });

    it('creates a transport post with text and redirects to the post page', () => {
        checkIntoNightjet();

        cy.get('textarea.textarea').type('Hello from Cypress!');
        cy.get('button[type="submit"].btn-primary').click();

        cy.url().should('match', /\/posts\/[a-f0-9-]+$/);
        cy.contains('Hello from Cypress!').should('be.visible');
        cy.contains('Karlsruhe Hbf').should('be.visible');
        cy.contains('Basel Bad Bf').should('be.visible');
    });

    it('shows the changed platform for a departure and its stopovers, and hides it when none is supplied', () => {
        cy.visit('/posts/transport/departures');
        cy.get('[name="departure-search"]').type('Karlsruhe Hbf');
        cy.contains('Karlsruhe').click();

        cy.contains('Pl. 7')
            .should('be.visible')
            .and('not.have.class', 'line-through');
        cy.get('.text-error')
            .should('be.visible')
            .contains('Pl. 8')
            .should('have.class', 'line-through');

        cy.contains('Nightjet').click();

        cy.contains('li', 'Karlsruhe Hbf').within(() => {
            cy.contains('Pl. 7')
                .should('be.visible')
                .and('not.have.class', 'line-through');
            cy.contains('Pl. 8')
                .should('be.visible')
                .and('have.class', 'line-through');
        });

        cy.contains('li', 'Zürich HB').within(() => {
            cy.contains('Pl.').should('not.exist');
        });
    });

    function checkIntoNightjet() {
        cy.visit('/posts/transport/departures');
        cy.get('[name="departure-search"]').type('Karlsruhe Hbf');
        cy.contains('Karlsruhe').click();
        cy.contains('Nightjet').click();
        cy.contains('Basel Bad Bf').click();
    }
});
