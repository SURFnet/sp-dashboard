Cypress.Commands.add('addAttribute', (name = 'givenName', motivation = 'we wants it') => {
    cy.get(`#dashboard_bundle_entity_type_attributes_${name}Attribute_requested`).click();
    cy.get(`#dashboard_bundle_entity_type_attributes_${name}Attribute_motivation`).type(motivation);
});

Cypress.Commands.add('addAllAttributes', (attributes) => {
    for (const attributeName of attributes) {
        cy.addAttribute(attributeName);
    }
});

Cypress.Commands.add('verifyAttributeMotivations', (attributes = []) => {
    for (const attributeName of attributes) {
        cy.contains(attributeName)
          .next()
          .contains('we wants it');
    }
});
