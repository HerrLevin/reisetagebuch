describe('Register', () => {
    beforeEach(() => {
        cy.resetDatabase();
    });

    it('displays the registration form', () => {
        cy.visit('/register');
        cy.get('#name').should('be.visible');
        cy.get('#username').should('be.visible');
        cy.get('#email').should('be.visible');
        cy.get('#password').should('be.visible');
        cy.get('#password_confirmation').should('be.visible');
        cy.get('button[type="submit"]').should('be.visible');
    });

    it('registers successfully and redirects to /home', () => {
        cy.visit('/register');

        cy.get('#name').type('New User');
        cy.get('#username').type('newuser');
        cy.get('#email').type('newuser@example.com');
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/home');
    });

    it('shows validation error for duplicate email', () => {
        cy.apiRegister();
        cy.visit('/register');

        cy.get('#name').type('Duplicate User');
        cy.get('#username').type('dupeuser');
        cy.get('#email').type('test@example.com');
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('password');
        cy.get('button[type="submit"]').click();

        cy.get('.text-error').should('be.visible');
    });

    it('shows validation error for password mismatch', () => {
        cy.visit('/register');

        cy.get('#name').type('Mismatch User');
        cy.get('#username').type('mismatchuser');
        cy.get('#email').type('mismatch@example.com');
        cy.get('#password').type('password');
        cy.get('#password_confirmation').type('Differentpassword');
        cy.get('button[type="submit"]').click();

        cy.get('.text-error').should('be.visible');
    });
});
