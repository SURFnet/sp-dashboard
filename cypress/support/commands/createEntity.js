Cypress.Commands.add('addMetadataUrl', (url = '') => {
    cy.get('#dashboard_bundle_entity_type_metadata_metadataUrl').type(url);
});

Cypress.Commands.add('addAcsLocation', (location = 'https://oidc.dev.support.surfconext.nl/saml/SSO') => {
    cy.get('#dashboard_bundle_entity_type_metadata_acsLocations').type(location);
    cy.get('#dashboard_bundle_entity_type_metadata_acsLocations .add_collection_entry').click();
});

Cypress.Commands.add('addEntityId', (id = 'https://tiffany.aching.do/id') => {
    cy.get('#dashboard_bundle_entity_type_metadata_entityId').type(id);
});

Cypress.Commands.add('selectNameIdFormat', (format = 'transient') => {
    switch (format) {
        case 'transient':
            cy.get('[for="dashboard_bundle_entity_type_metadata_nameIdFormat_0"]').click();
            break;
        case 'persistent':
            cy.get('[for="dashboard_bundle_entity_type_metadata_nameIdFormat_1"]').click();
            break;
    }
});

Cypress.Commands.add('addCertificate', (certificate = '') => {
    cy.get('#dashboard_bundle_entity_type_metadata_certificate').type(certificate);
});

Cypress.Commands.add('addLogoUrl', (url = 'https://generative-placeholders.glitch.me/image?width=600&height=300') => {
    cy.get('#dashboard_bundle_entity_type_metadata_logoUrl').type(url);
});

Cypress.Commands.add('addNameNl', (name = 'Tiffany Aching') => {
    cy.get('#dashboard_bundle_entity_type_metadata_nameNl').type(name);
});

Cypress.Commands.add('addDescriptionNl', (description = 'Ik, wat?  Geen pagina over Tiffany Aching in het Nederlands?  Ik ben verontwaardigd ende alsook zwaar op mijn teen getorten!  Schande!  Schreeuw het van de daken: SCHANDE!  Maar ik ben wel te lui om er één toe te voegen, dat dan weer wel.') => {
    cy.get('#dashboard_bundle_entity_type_metadata_descriptionNl').type(description);
});

Cypress.Commands.add('addNameEn', (name = 'Tiffany Aching') => {
    cy.get('#dashboard_bundle_entity_type_metadata_nameEn').type(name);
});

Cypress.Commands.add('addDescriptionEn', (description = 'Tiffany Aching is a fictional character in Terry Pratchett\'s satirical Discworld series of fantasy novels. Her name in Nac Mac Feegle is Tir-far-thóinn or "Land Under Wave".') => {
    cy.get('#dashboard_bundle_entity_type_metadata_descriptionEn').type(description);
});

Cypress.Commands.add('addApplicationUrl', (url = 'https://tiffany.aching.do') => {
    cy.get('#dashboard_bundle_entity_type_metadata_applicationUrl').type(url);
});

Cypress.Commands.add('addEulaUrl', (url = 'https://tiffany.aching.do/eula') => {
    cy.get('#dashboard_bundle_entity_type_metadata_eulaUrl').type(url);
});

Cypress.Commands.add('addAdministrativeContact', (firstName = 'Franz', lastName = 'Kafka', email = 'franz@kafka.org', phone = '') => {
    cy.get('#dashboard_bundle_entity_type_contactInformation_administrativeContact_firstName').type(firstName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_administrativeContact_lastName').type(lastName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_administrativeContact_email').type(email)
    cy.fillPhone('#dashboard_bundle_entity_type_contactInformation_administrativeContact_phone', phone);
});

Cypress.Commands.add('addTechnicalContact', (firstName = 'Ada', lastName = 'Lovelace', email = 'ada@lovelace.do', phone = '') => {
    cy.get('#dashboard_bundle_entity_type_contactInformation_technicalContact_firstName').type(firstName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_technicalContact_lastName').type(lastName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_technicalContact_email').type(email);
    cy.fillPhone('#dashboard_bundle_entity_type_contactInformation_technicalContact_phone', phone);
});

Cypress.Commands.add('addSupportContact', (firstName = 'Granny', lastName = 'Weatherwax', email = 'granny@beez.biz', phone = '') => {
    cy.get('#dashboard_bundle_entity_type_contactInformation_supportContact_firstName').type(firstName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_supportContact_lastName').type(lastName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_supportContact_email').type(email);
    cy.fillPhone('#dashboard_bundle_entity_type_contactInformation_supportContact_phone', phone);
});

Cypress.Commands.add('fillPhone', (id, phone = '') => {
    if (phone) {
        cy.get(id).type(phone);
    }
});

Cypress.Commands.add('addComment', (comment = 'ya always know just what ta say, don\'tcha?') => {
    cy.get('#dashboard_bundle_entity_type_comments_comments').type(comment);
});

Cypress.Commands.add('fillInCreateSamlForm', (attributes = [], entityId = '') => {
    cy.addAcsLocation();
    cy.addLogoUrl();
    cy.addNameNl();
    cy.addDescriptionNl();
    cy.addNameEn();
    cy.addDescriptionEn();
    cy.addAdministrativeContact();
    cy.addTechnicalContact();
    cy.addSupportContact();
    cy.addAllAttributes(attributes);
    cy.addComment();

    // using an if else here because i cannot just return from the if to avoid the else (cypress breaks on that).
    if (entityId) {
        cy.addEntityId(entityId);
        cy.addApplicationUrl(`${entityId}/app`);
        cy.addEulaUrl(`${entityId}/eula`);
    } else {
        cy.addEntityId();
        cy.addApplicationUrl();
        cy.addEulaUrl();
    }
});

Cypress.Commands.add('verifyCreation', () => {
    cy.viewEntity();
    cy.checkContainsValue('ACS location', 'https://oidc.dev.support.surfconext.nl/saml/SSO');
    cy.checkCorrectTextValue('Entity ID', 'https://tiffany.aching.do/id');
    cy.checkCorrectTextValue('Logo URL', 'https://generative-placeholders.glitch.me/image?width=600&height=300');
    cy.checkCorrectTextValue('Name NL', 'Tiffany Aching');
    cy.checkCorrectTextValue('Description NL', 'Ik, wat?  Geen pagina over Tiffany Aching in het Nederlands?  Ik ben verontwaardigd ende alsook zwaar op mijn teen getorten!  Schande!  Schreeuw het van de daken: SCHANDE!  Maar ik ben wel te lui om er één toe te voegen, dat dan weer wel.');
    cy.checkCorrectTextValue('Name EN', 'Tiffany Aching');
    cy.checkCorrectTextValue('Description EN', 'Tiffany Aching is a fictional character in Terry Pratchett\'s satirical Discworld series of fantasy novels. Her name in Nac Mac Feegle is Tir-far-thóinn or "Land Under Wave".');
    cy.checkCorrectTextValue('Application URL', 'https://tiffany.aching.do');
    cy.checkCorrectTextValue('EULA URL', 'https://tiffany.aching.do/eula');
    cy.checkContact('Administrative contact', 'Franz', 'Kafka', 'franz@kafka.org');
    cy.checkContact('Technical contact', 'Ada', 'Lovelace', 'ada@lovelace.do');
    cy.checkContact('Support contact', 'Granny', 'Weatherwax', 'granny@beez.biz');
});

Cypress.Commands.add('clickPublishButton', () => {
    cy.get('#dashboard_bundle_entity_type_publishButton').click();
});

Cypress.Commands.add('openCreateEntityModal', (environment = 'test', serviceID = 1) => {
    cy.selectService(serviceID);
    cy.contains(`New ${environment} entity`).first().click();
    cy.get(`[id^="add-for-${environment}"]:checked ~ .blocker .modal[data-for^="add-for-${environment}"]`).contains('Create').click();
});

Cypress.Commands.add('createEntity', (attributes = [], environment = 'test', entityId = '') => {
    cy.openCreateEntityModal(environment, 2);
    cy.fillInCreateSamlForm(attributes, entityId);
    cy.clickPublishButton();
});
