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

Cypress.Commands.add('addMemberRole', (isMemberOf = 'eddy-wally') => {
    cy.get('#add-attribute').select('urn:mace:dir:attribute-def:isMemberOf');
    cy.get('input[name="urn:mace:dir:attribute-def:isMemberOf"]').type(isMemberOf);
});

Cypress.Commands.add('checkForConsent', () => {
    cy.get('body').then((body) => {
        const isConsentPage = body.find('#accept').length;
        if (isConsentPage) {
            cy.get('#accept').submit();
        }
    });
});

Cypress.Commands.add('submitLoginForms', () => {
    cy.get('.login-form').submit();
    cy.checkForConsent();
});

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

Cypress.Commands.add('login', (username = 'Tiffany', pass = 'Aching', submit = true, url = 'https://spdashboard.vm.openconext.org') => {
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

// ALERT: this can become an eternal loop.  Only use this function after trying to log in to manage.
Cypress.Commands.add('checkForManage', () => {
    cy.get('body').then((body) => {
        const isManagePage = body.find('.search-input').length;
        if (!isManagePage) {
            cy.checkForConsent();
            cy.wait(100);
            cy.checkForManage();
        }
    });
});

Cypress.Commands.add('loginToManage', (url = 'https://manage.vm.openconext.org') => {
    cy.visit(url);
    cy.checkForManage();
});

Cypress.Commands.add('addMetadataUrl', (url = '') => {
    cy.get('#dashboard_bundle_entity_type_metadata_metadataUrl').type(url);
});

Cypress.Commands.add('addAcsLocation', (location = 'https://oidc.dev.support.surfconext.nl/saml/SSO') => {
    cy.get('#dashboard_bundle_entity_type_metadata_acsLocation').type(location);
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
    cy.get('#dashboard_bundle_entity_type_contactInformation_administrativeContact_email').type(email);
    if (phone) {
        cy.get('#dashboard_bundle_entity_type_contactInformation_administrativeContact_phone').type(phone);
    }
});

Cypress.Commands.add('addTechnicalContact', (firstName = 'Ada', lastName = 'Lovelace', email = 'ada@lovelace.do', phone = '') => {
    cy.get('#dashboard_bundle_entity_type_contactInformation_technicalContact_firstName').type(firstName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_technicalContact_lastName').type(lastName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_technicalContact_email').type(email);
    if (phone) {
        cy.get('#dashboard_bundle_entity_type_contactInformation_technicalContact_phone').type(phone);
    }
});

Cypress.Commands.add('addSupportContact', (firstName = 'Granny', lastName = 'Weatherwax', email = 'granny@beez.biz', phone = '') => {
    cy.get('#dashboard_bundle_entity_type_contactInformation_supportContact_firstName').type(firstName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_supportContact_lastName').type(lastName);
    cy.get('#dashboard_bundle_entity_type_contactInformation_supportContact_email').type(email);
    if (phone) {
        cy.get('#dashboard_bundle_entity_type_contactInformation_supportContact_phone').type(phone);
    }
});

Cypress.Commands.add('addAttribute', (name = 'givenName', motivation = 'we wants it') => {
    cy.get(`#dashboard_bundle_entity_type_attributes_${name}Attribute_requested`).click();
    cy.get(`#dashboard_bundle_entity_type_attributes_${name}Attribute_motivation`).type(motivation);
});

Cypress.Commands.add('addAllAttributes', (attributes) => {
    for (const attributeName of attributes) {
        cy.addAttribute(attributeName);
    }
});

Cypress.Commands.add('addComment', (comment = 'ya always know just what ta say, don\'tcha?') => {
    cy.get('#dashboard_bundle_entity_type_comments_comments').type(comment);
});

Cypress.Commands.add('fillInCreateSamlForm', (attributes = [], entityId = '') => {
    cy.addAcsLocation();
    cy.addLogoUrl();
    cy.addDescriptionNl();
    cy.addDescriptionEn();
    cy.addAdministrativeContact();
    cy.addTechnicalContact();
    cy.addSupportContact();
    cy.addAllAttributes(attributes);
    cy.addComment();

    // using an if else here because i cannot just return from the if to avoid the else (cypress breaks on that).
    if (entityId) {
        cy.addEntityId(`https://unseen.university.org/${entityId}/id`);
        cy.addApplicationUrl(`https://unseen.university.org/${entityId}`);
        cy.addEulaUrl(`https://unseen.university.org/${entityId}/eula`);
        cy.addNameNl(entityId);
        cy.addNameEn(entityId);
    } else {
        cy.addEntityId();
        cy.addApplicationUrl();
        cy.addEulaUrl();
        cy.addNameNl();
        cy.addNameEn();
    }
});

Cypress.Commands.add('clickPublishButton', () => {
    cy.get('#dashboard_bundle_entity_type_publishButton').click();
})

Cypress.Commands.add('goToServiceOverview', () => {
    cy.get('.navigation').contains('Service overview').click();
})

Cypress.Commands.add('openCreateEntityModal', (environment = 'test', serviceID = 1) => {
    cy.selectService(serviceID);
    cy.contains(`New ${environment} entity`).first().click();
    cy.get(`[id^="add-for-${environment}"]:checked ~ .blocker .modal[data-for^="add-for-${environment}"]`).contains('Create').click();
});

Cypress.Commands.add('createEntity', (attributes, environment = 'test', entityId = '') => {
    cy.openCreateEntityModal();
    cy.fillInCreateSamlForm(attributes, entityId);
    cy.clickPublishButton();
});

Cypress.Commands.add('loadEntityView', (environment = 'test', id = 'https://tiffany.aching.do/id') => {
    cy.goToServiceOverview();
    cy.get(`.service-status-entities-table.${environment}-entities`)
      .contains(id)
      .next()
      .next()
      .next()
      .contains('View')
      .click({force: true});
});

Cypress.Commands.add('verifyAttributeMotivations', (attributes = []) => {
    for (const attributeName of attributes) {
        cy.contains(attributeName)
          .next()
          .contains('we wants it');
    }
})
