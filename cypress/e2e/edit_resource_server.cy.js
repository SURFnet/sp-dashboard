context('Edit resource server', () => {
    const clientId = 'https://tiffany.aching.do/rs/edit';
    // Manage stores the client id of an OIDC entity without its scheme, the overview and Manage use this value
    const manageEntityId = 'tiffany.aching.do/rs/edit';

    before(() => {
        cy.deleteEntity('oauth20_rs', manageEntityId);
        cy.login();
        cy.createResourceServer(clientId, manageEntityId);
    });

    // Manage only accepts the keys that are defined for the entity type. Editing a resource server used to send a key
    // that is not allowed for resource servers, so Manage rejected the change and the user saw an error.
    it('publishes the changes of an existing resource server to Manage', () => {
        cy.selectService(2);
        cy.editEntity('test', manageEntityId);
        cy.changeNameEn('Edited resource server');
        cy.addComment('editing a resource server');
        cy.clickPublishButton();

        cy.contains('Your changes were saved!');
        cy.get('.message.error').should('not.exist');

        cy.manageEntityMetaDataField('oauth20_rs', manageEntityId, 'name:en')
            .should('equal', 'Edited resource server');
    });

    after(() => {
        cy.deleteEntity('oauth20_rs', manageEntityId);
    });
});
