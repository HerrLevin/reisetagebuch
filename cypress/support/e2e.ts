import './commands';

// answer the location history prompt up front so its modal never covers the page
Cypress.on('window:before:load', (win) => {
    win.localStorage.setItem(
        'locationHistory',
        JSON.stringify({ allowHistory: false }),
    );
});
