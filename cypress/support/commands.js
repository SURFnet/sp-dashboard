// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

Cypress.Commands.add('fillUsername', (username = 'Granny') => {
    cy.get('#username').type(username);
});

Cypress.Commands.add('fillPassword', (pass = 'Weatherwax') => {
    cy.get('#password').type(pass);
});

Cypress.Commands.add('loginToService', (serviceID = 1) => {
    cy.loginWithMemberRole().then(() => {
        cy.wait(300).then(() => {
            cy.selectService(serviceID);
        });
    });
});

Cypress.Commands.add('addMemberRole', (isMemberOf = 'eddy-wally') => {
    cy.get('#add-attribute').select('urn:mace:dir:attribute-def:isMemberOf');
    cy.get('input[name="urn:mace:dir:attribute-def:isMemberOf"]').type(isMemberOf);
});

Cypress.Commands.add('submitLoginForms', () => {
    cy.get('.login-form').submit();
    cy.get('body').then((body) => {
        const isConsentPage = body.find('#accept').length;
        if (isConsentPage) {
            cy.get('#accept').submit();
        }
    });
})

Cypress.Commands.add('removeSFToolbar', () => {
    cy.get('body').then((body) => {
        const hasToolbar = body.find('.sf-toolbar').length;
        if (hasToolbar) {
            body.find('.sf-toolbar').remove();
        }
    });
})

Cypress.Commands.add('selectService', (serviceId = 1) => {
    cy.visit(`https://spdashboard.vm.openconext.org/service/${serviceId}`);
});

Cypress.Commands.add('login', (url = 'https://spdashboard.vm.openconext.org', username = 'Tiffany', pass = 'Aching', submit = true) => {
    cy.visit(url);
    cy.fillUsername(username);
    cy.fillPassword(pass);
    if (submit) {
        cy.submitLoginForms();
    }
});

Cypress.Commands.add('loginWithMemberRole', (url = '', username = 'John', pass = 'Doe', isMemberOf = 'eddy-wally') => {
    cy.login(username, pass, false);
    cy.addMemberRole(isMemberOf);
    cy.submitLoginForms().then(() => {
        cy.removeSFToolbar();
    });
});

Cypress.Commands.add('loginToManage', (url = 'https://manage.vm.openconext.org', username = 'Terry', pass = 'Veterinari') => {
    cy.login(url, username, pass);
});
