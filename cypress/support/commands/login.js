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

Cypress.Commands.add('checkForConsent', () => {
    cy.get('body').then((body) => {
        const isConsentPage = body.find('#accept').length;
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
    }
});

Cypress.Commands.add('loginWithMemberRole', (url = '', username = 'John', pass = 'Doe', isMemberOf = 'urn:collab:org:surf.nl') => {
    cy.login(username, pass, false);
    cy.addMemberRole(isMemberOf);
    cy.submitLoginForms().then(() => {
        cy.removeSFToolbar();
    });
});

Cypress.Commands.add('loginToService', (serviceID = 1) => {
    cy.loginWithMemberRole().then(() => {
        cy.wait(300).then(() => {
            cy.selectService(serviceID);
        });
    });
});

Cypress.Commands.add('loginToServiceWithModal', (serviceID = 1) => {
    cy.loginWithMemberRole().then(() => {
        cy.wait(300).then(() => {
            cy.selectService(serviceID);
            cy.contains('New production entity').first().click();
        });
    });
});
