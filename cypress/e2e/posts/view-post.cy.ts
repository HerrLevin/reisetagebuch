describe('View Post', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('displays post body and author', () => {
        cy.apiRegister().then((auth) => {
            cy.apiCreateTextPost(
                auth.token,
                'A post to view',
                'public',
            ).then((post) => {
                cy.visit(`/posts/${(post as Record<string, unknown>).id}`);

                cy.contains('A post to view').should('be.visible');
            });
        });
    });
});
