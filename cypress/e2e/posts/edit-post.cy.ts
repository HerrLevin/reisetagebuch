import { storeAuth } from '../../support/commands';

describe('Edit Post', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('edits a post and shows updated content', () => {
        cy.apiRegister().then((auth) => {
            cy.apiCreateTextPost(
                auth.token,
                'Original text',
                'public',
            ).then((post) => {
                const postId = (post as Record<string, unknown>).id;
                cy.visit(`/posts/${postId}/edit`, {
                    onBeforeLoad(win) {
                        storeAuth(win, auth)
                    },
                });

                cy.get('textarea.textarea').should('have.value', 'Original text');

                cy.get('textarea.textarea').clear().type('Updated text');
                cy.get('button[type="submit"].btn-primary').click();

                cy.url().should('include', `/posts/${postId}`);
                cy.contains('Updated text').should('be.visible');
            });
        });
    });
});
