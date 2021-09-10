Cypress.Commands.add('fillUsername', (username = 'Granny') => {
    cy.get('#username').type(username);
});

Cypress.Commands.add('fillPassword', (pass = 'Weatherwax') => {
    cy.get('#password').type(pass);
});

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

Cypress.Commands.add('login', (username = 'Tiffany', pass = 'Aching', submit = true, url = 'https://spdashboard.vm.openconext.org') => {
    cy.visit(url);
    cy.fillUsername(username);
    cy.fillPassword(pass);
    if (submit) {
        cy.submitLoginForms();
        cy.checkForLoginComplete(url);
    }
});

Cypress.Commands.add('loginWithMemberRole', (url = '', username = 'John', pass = 'Doe', isMemberOf = 'urn:collab:org:surf.nl') => {
    cy.login(username, pass, false);
    cy.addMemberRole(isMemberOf);
    cy.submitLoginForms().then(() => {
        cy.checkForLoginComplete(url);
    });
});
