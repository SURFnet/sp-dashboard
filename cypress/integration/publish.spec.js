import {attributes, attributesTitles} from '../fixtures/variables';

context('Consent verify a11y', () => {
    beforeEach(() => {
        cy.loginToService();
    });

    // Elke keer als een productie entity wordt gepubliceerd, dient de coin:exclude_from_push aan te staan, uitgezonderd bij een secret reset voor een entity waar deze al uit staat.
    it.skip('coin:exclude_from_push is on when publishing', () => {
        cy.visit('https://spdashboard.vm.openconext.org/service/3', { failOnStatusCode: false });
        cy.url().should('not.eq', 'https://spdashboard.vm.openconext.org/service/3')
    });

    // Als een entity op productie wordt gepubliceerd, mag daar geen ACL op gezet worden: dus
    // "allowedEntities" : [ ],
    // "allowedall" : true,
    it.skip('when publishing an entity on production, no ACL can be set', () => {
        cy.createEntity(attributes, 'production');
    });

    // Als ik een entity aanmaak, dan moeten alle attributen die ik aanvink ook zichtbaar zijn als die entity "view"
    // Als ik een opmerking plaats moet die mee in het comment veld van Manage
    it('when creating an entity, all attributes i add on creation should be visible in the entity view and the comment should be in the comment field in Manage', () => {
        cy.createEntity(attributes);
        cy.loadEntityView();
        cy.verifyAttributeMotivations(attributesTitles);
        cy.loginToManage();
        cy.checkForConsent();
        cy.get('.search-input').type('Tiffany Aching');
        cy.get('.matched').contains('Tiffany Aching').click();
        cy.contains('Revision notes').next().contains('ya always know just what ta say, don\'tcha?');
    });
});
