
Cypress.Commands.add('waitForSpDashboardLoginForm', (username = 'Tiffany', pass = 'Aching', tries = 0) => {
    cy.get('body', {log: false}).then(($body) => {
        const isLoggedIn = $body.find('.navigation, .service-status-container').length > 0;
        const isLoginPage = $body.find('#username').length > 0 && $body.find('.login-form').length > 0;

        if (isLoggedIn) {
            return;
        }

        if (isLoginPage) {
            cy.get('#username').type(username);
            cy.get('#password').type(pass);
            cy.get('.login-form').submit();
            cy.url({timeout: 15000}).should('include', 'spdashboard.dev.openconext.local');
            return;
        }

        if (tries >= 15) {
            throw new Error('Mujina login form did not appear after selecting Dummy IdP');
        }

        cy.wait(1000, {log: false});
        cy.waitForSpDashboardLoginForm(username, pass, tries + 1);
    });
});

Cypress.Commands.add('checkForConsent', () => {
    cy.get('body').then(($body) => {
        const isConsentPage = $body.find('.consent').length;
        if (isConsentPage) {
            cy.get('#accept').submit();
        }
    });
});

Cypress.Commands.add('login', (username = 'Tiffany', pass = 'Aching') => {
    // Trigger the SP-initiated SAML flow: spdashboard → engine WAYF
    cy.visit('https://spdashboard.dev.openconext.local');
    cy.get('body').then(($body) => {
        // If already logged in, the WAYF IdP selector won't be shown — skip login
        if ($body.find('[data-title="Dummy IdP"]').length === 0) {
            return;
        }
        // Click Dummy IdP (Mujina) on the engine WAYF
        // chromeWebSecurity:false allows cross-origin interaction without cy.origin()
        cy.get('[data-title="Dummy IdP"]').first().click({force: true});
        cy.waitForSpDashboardLoginForm(username, pass);
    });
});
