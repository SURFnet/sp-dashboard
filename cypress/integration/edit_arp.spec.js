import {attributes} from '../fixtures/variables';

context('Edit ARP', () => {
  before(() => {
    cy.loginWithMemberRole('', 'John', 'Doe', 'eddy-wally');
    cy.selectService(1);
    cy.contains('New production entity').first().click();
    cy.createEntity(attributes);
  });

  // Als ik een entity (oidcng en saml) ga wijzigen, en er staan onbekende attributen in de ARP in Manage, dan moeten deze blijven staan als ik op publish klik
  it('unknown ARP attributes remain in Manage after an edit', () => {
    cy.loginToManageAndSelectTiffanyAching();
    cy.goToArpTab();
    cy.addSurfCrmId();
    cy.visit('https://spdashboard.vm.openconext.org/service/1');
    cy.editEntity();
    cy.addPersonalCodeAttribute();
    cy.addComment('testing editing an attribute');
    cy.clickPublishButton();
    cy.loginToManageAndSelectTiffanyAching();
    cy.goToArpTab();
    cy.checkSurfCrmIdIsChecked();
  });

  after(() => {
    cy.deleteEntity();
  });
});
