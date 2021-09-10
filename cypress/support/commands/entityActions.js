Cypress.Commands.add('goToServiceOverview', () => {
    cy.get('.navigation').contains('Service overview').click();
});

Cypress.Commands.add('doEntityAction', (action, environment = 'test', id = 'https://tiffany.aching.do/id') => {
    cy.selectService();
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

Cypress.Commands.add('deleteEntity', (environment = 'test', id = 'https://tiffany.aching.do/id') => {
    cy.doEntityAction('Delete', environment, id);
    cy.get('#dashboard_bundle_delete_entity_type_delete').then((button) => {
        button.trigger('click');
    });
});

Cypress.Commands.add('editWhitelist', (id = 'https://tiffany.aching.do/id') => {
    cy.doEntityAction('Edit IdP whitelist', 'test', id);
});
