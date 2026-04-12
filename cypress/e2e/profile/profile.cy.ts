describe('Profile', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('displays the user name and @username', () => {
        cy.apiRegister().then(() => {
            cy.visit('/profile/testuser');

            cy.contains('@testuser').should('be.visible');
        });
    });

    it("displays the user's posts", () => {
        cy.apiRegister().then((auth) => {
            cy.apiCreateTextPost(auth.token, 'Profile post', 'public').then(
                () => {
                    cy.visit('/profile/testuser');

                    cy.contains('Profile post').should('be.visible');
                },
            );
        });
    });
});
