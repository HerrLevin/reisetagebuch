describe('Like Post', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('likes and unlikes a post', () => {
        cy.apiRegister().then((auth) => {
            cy.apiCreateTextPost(
                auth.token,
                'Post to like',
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

                cy.contains('Post to like').should('be.visible');

                // Click the like button (heart icon)
                cy.get('.lucide-heart-icon')
                    .parent()
                    .click();

                // Verify the like count changed
                cy.get('.lucide-heart-icon').parent().parent().should('be.visible').should('contain', '1');

                // Click again to unlike
                cy.get('.lucide-heart-icon').parent().click();

                // Verify it went back to 0
                cy.get('.lucide-heart-icon').parent().parent().should('not.contain.text');
            });
        });
    });
});
