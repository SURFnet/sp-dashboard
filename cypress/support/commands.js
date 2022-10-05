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
Cypress.Commands.add('removeSFToolbar', () => {
    cy.get('body').then((body) => {
        const hasToolbar = body.find('.sf-toolbar').length;
        if (hasToolbar) {
            body.find('.sf-toolbar').remove();
        }
    });
});

Cypress.Commands.add('clickSubmit', () => {
    cy.contains('Submit').click();
});

Cypress.Commands.add('selectService', (serviceId = 1) => {
    cy.visit(`https://spdashboard.vm.openconext.org/service/${serviceId}`);
});

Cypress.Commands.add('checkCorrectTextValue', (label, value) => {
    cy.contains(label).next().should('have.text', value);
});

Cypress.Commands.add('checkContainsValue', (label, value) => {
    cy.contains(label).next().should('contain.text', value);
});

Cypress.Commands.add('checkContact', (contact, firstName, lastName, email, phone = '') => {
    cy.contains(contact)
      .next().contains('First name').next().should('have.text', firstName);
    cy.contains(contact)
      .next().contains('Last name').next().should('have.text', lastName);
    cy.contains(contact)
      .next().contains('Email').next().should('have.text', email);
    if (phone) {
        cy.contains(contact)
          .next().contains('Phone').next().should('have.text', email);
    }
})
