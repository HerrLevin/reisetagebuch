describe('Delete Post', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('deletes a post via the context menu', () => {
        cy.apiRegister().then((auth) => {
            cy.apiCreateTextPost(
                auth.token,
                'Post to delete',
                'public',
            ).then((post) => {
                const postId = (post as Record<string, unknown>).id;
                cy.visit(`/posts/${postId}`, {
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

                cy.contains('Post to delete').should('be.visible');

                // Open the dropdown context menu
                cy.get('.flex > .dropdown').first().click()

                // Click the delete option in the dropdown
                cy.get('.dropdown-content').should('be.visible');
                cy.get('.text-red-500').last().click();

                // Confirm deletion in the modal
                cy.get('button.btn-error').click();

                // Should navigate away from the deleted post
                cy.url().should('not.include', `/posts/${postId}`);
            });
        });
    });
});
