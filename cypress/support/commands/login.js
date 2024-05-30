

Cypress.Commands.add('addMemberRole', (isMemberOf = 'urn:collab:org:surf.nl') => {
    cy.get('#add-attribute').select('urn:mace:dir:attribute-def:isMemberOf');
    cy.get('input[name="urn:mace:dir:attribute-def:isMemberOf"]').type(isMemberOf);
});

Cypress.Commands.add('submitLoginForms', () => {
    cy.get('.login-form').submit();
    cy.checkForConsent();
});

Cypress.Commands.add('checkForLoginComplete', (url) => {
    cy.get('html').then(($html) => {
        const li = $html.find('.navigation .first');

        if (!li) {
            cy.wait(400);
            cy.checkForConsent();
            cy.checkForLoginComplete(url);
        } else {
            cy.contains('Logout');
        }
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
    cy.origin('https://mujina-idp.dev.openconext.local', {args: {username, pass}}, ({username, pass}) => {
        cy.visit('/login');
        cy.get('#username').type(username);
        cy.get('#password').type(pass);
        cy.get('.login-form').submit();
    });
    const url = 'https://spdashboard.dev.openconext.local';
    cy.wait(400);
    cy.visit({
        url: url,
        failOnStatusCode: false
    });
});
