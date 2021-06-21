import {attributes, attributesTitles} from '../fixtures/variables';

context('Consent verify a11y', () => {
    before(() => {
        cy.loginToService();
        cy.createEntity(attributes);
    });

    // Als ingelogde gebruiker van dienst X kan ik geen edits doen in dienst Y (door het raden van de url van een andere service bijvoorbeeld)
    it('is not possible to edit another service', () => {
        cy.visit('https://spdashboard.vm.openconext.org/service/3', { failOnStatusCode: false });
        cy.contains('Unable to find service');
    });

    // Als ik een entity (oidcng en saml) ga wijzigen, en er staan onbekende attributen in de ARP in Manage, dan moeten deze blijven staan als ik op publish klik
    it.only('unknown ARP attributes remain in Manage after an edit', () => {
        cy.loginToManageAndSelectTiffanyAching();
        cy.goToArpTab();
        cy.addSurfCrmId();
        cy.editEntity();
        cy.addPersonalCodeAttribute();
        cy.addComment('testing editing an attribute');
        cy.clickPublishButton();
        cy.loginToManageAndSelectTiffanyAching();
        cy.goToArpTab();
        cy.checkSurfCrmIdIsChecked();
    });

    // Als ik de ACL wijzig van een bestaande entity dan moeten alle andere velden ongemoeid blijven (ik kan een manage export aanleveren met een entity waarin heel veel aanpassingen zijn gedaan eventueel
    it('i can change the ACL of an existing entity without changing the other fields', () => {
        cy.selectService();
        cy.editWhitelist();
        cy.deselectAllWhitelist();
        cy.loginToManageAndSelectTiffanyAching();
        cy.goToWhitelistingTab();
        cy.checkAllWhitelistIsUnchecked();
        cy.viewEntity();
        cy.verifyCreation();
        cy.verifyAttributeMotivations(attributesTitles);
    });

    after(() => {
        cy.deleteEntity();
    });
});
