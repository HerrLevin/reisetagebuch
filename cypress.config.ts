import { defineConfig } from 'cypress';

export default defineConfig({
    e2e: {
        baseUrl: 'http://localhost',
        supportFile: 'cypress/support/e2e.ts',
        specPattern: 'cypress/e2e/**/*.cy.ts',
        defaultCommandTimeout: 10000,
        retries: {
            runMode: 2,
            openMode: 0,
        },
        screenshotOnRunFailure: true,
        video: true,
    },
});
