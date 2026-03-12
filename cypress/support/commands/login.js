
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
        // On the Mujina login page (with a real SAMLRequest), fill in credentials
        cy.get('#username').type(username);
        cy.get('#password').type(pass);
        cy.get('.login-form').submit();
        // Wait for the full redirect chain (Mujina → engine → spdashboard) to complete
        cy.url({timeout: 15000}).should('include', 'spdashboard.dev.openconext.local');
    });
});
