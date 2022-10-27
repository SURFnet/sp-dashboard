Cypress.Commands.add('goToServiceOverview', () => {
    cy.get('.navigation').contains('Service overview').click();
});

Cypress.Commands.add('doEntityAction', (action, environment = 'test', id = 'https://tiffany.aching.do/id') => {
    cy.get(`.service-status-entities-table.${environment}-entities`)
      .contains(id)
      .next()
      .next()
      .next()
      .contains(action)
      .click({force: true});
});

Cypress.Commands.add('editEntity', (environment = 'test', id = 'https://tiffany.aching.do/id') => {
    cy.doEntityAction('Edit', environment, id);
});

Cypress.Commands.add('viewEntity', (environment = 'test', id = 'https://tiffany.aching.do/id') => {
    cy.doEntityAction('View', environment, id);
});

Cypress.Commands.add('editWhitelist', (id = 'https://tiffany.aching.do/id') => {
    cy.doEntityAction('Edit IdP whitelist', 'test', id);
});

Cypress.Commands.add('openConnectionRequest', (id) => {
    cy.doEntityAction('Create connection request', 'production', id);
});
Cypress.Commands.add('fillConnectionRequestForm', () => {
    cy.get('#connection_request_container_connectionRequests___name___institution')
        .type('Harderwijk University');
    cy.get('#connection_request_container_connectionRequests___name___name')
        .type('Johny Walker');
    cy.get('#connection_request_container_connectionRequests___name___email')
        .type('jay-dob@harderwijk.nl');
    cy.get('.add_collection_entry').click();
});
