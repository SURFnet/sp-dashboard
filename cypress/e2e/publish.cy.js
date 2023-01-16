import {attributes, attributesTitles} from '../fixtures/variables';

context('Publish functionality', () => {
    beforeEach(() => {
        cy.login();
        cy.selectService(1);
        cy.contains('New production entity').first().click();
    });

    // Elke keer als een productie entity wordt gepubliceerd, dient de coin:exclude_from_push aan te staan, uitgezonderd bij een secret reset voor een entity waar deze al uit staat.
    it('coin:exclude_from_push is on when publishing', () => {
        cy.selectService();
        cy.createEntity(attributes, 'production', 'https://tiffany.aching.do/id/1');
        cy.loginToManageAndSelectTiffanyAching();
        cy.checkExcludeFromPushIsChecked();
        cy.deleteOnManage();
    });

    // Als een entity op productie wordt gepubliceerd, mag daar geen ACL op gezet worden: dus
    // "allowedEntities" : [ ],
    // "allowedall" : true,
    it('when publishing an entity on production, no ACL can be set', () => {
        cy.selectService();
        cy.createEntity(attributes, 'production', 'https://tiffany.aching.do/id/2');
        cy.loginToManageAndSelectTiffanyAching();
        cy.goToWhitelistingTab();
        cy.checkAllWhitelistIsChecked();
        cy.deleteOnManage()
    });

    // Als ik een entity aanmaak, dan moeten alle attributen die ik aanvink ook zichtbaar zijn als die entity "view"
    // Als ik een opmerking plaats moet die mee in het comment veld van Manage
    it('when creating an entity, all attributes i add on creation should be visible in the entity view and the comment should be in the comment field in Manage', () => {
        cy.selectService();
        cy.createEntity(attributes, 'test', 'https://tiffany.aching.do/id/3');
        cy.viewEntity();
        cy.verifyAttributeMotivations(attributesTitles);
        cy.loginToManageAndSelectTiffanyAching();
        cy.checkRevisionNote();
        cy.deleteOnManage()
    });
});
