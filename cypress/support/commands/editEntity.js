Cypress.Commands.add('addPersonalCodeAttribute', (motivation = 'the chalk is my home') => {
    cy.get('#dashboard_bundle_entity_type_attributes_personalCodeAttribute_requested').click();
    cy.get('#dashboard_bundle_entity_type_attributes_personalCodeAttribute_motivation').type(motivation);
});

Cypress.Commands.add('deselectAllWhitelist', () => {
    cy.get('#acl_entity_selectAll').click();
    cy.get('#acl_entity_save').click();
})
