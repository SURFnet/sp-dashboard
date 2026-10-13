Cypress.Commands.add('createResourceServer', (clientId, manageEntityId, serviceId = 2) => {
    cy.visit(`https://spdashboard.dev.openconext.local/entity/create/${serviceId}/oauth20_rs/test`);
    cy.get('#dashboard_bundle_entity_type_metadata_clientId').type(clientId);
    cy.addNameNl('Resource server');
    cy.addDescriptionNl('Resource server voor de end-to-end tests');
    cy.addNameEn('Resource server');
    cy.addDescriptionEn('Resource server for the end-to-end tests');
    cy.addAdministrativeContact();
    cy.addTechnicalContact();
    cy.addSupportContact();
    cy.clickPublishButton();
    cy.waitForEntityCreationToFinish();
    cy.waitForManageEntity('oauth20_rs', manageEntityId);
});

Cypress.Commands.add('changeNameEn', (name) => {
    cy.get('#dashboard_bundle_entity_type_metadata_nameEn').clear().type(name);
});
