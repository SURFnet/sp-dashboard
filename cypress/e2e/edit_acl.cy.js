import {attributes, attributesTitles} from '../fixtures/variables';

context('Edit ACL', () => {
  before(() => {
    cy.login();
    cy.createEntity('https://entityId.example.com', 'urn:mace:team', 'Foobar Service', {'attr1': 'foobar', 'attr2': 'bar'});
    // cy.selectService(2);
    // cy.contains('New production entity').first().click();
    // cy.createEntity(attributes);
  });

  // Als ik de ACL wijzig van een bestaande entity dan moeten alle andere velden ongemoeid blijven (ik kan een manage export aanleveren met een entity waarin heel veel aanpassingen zijn gedaan eventueel
  it('i can change the ACL of an existing entity without changing the other fields', () => {
    // cy.selectService(2);
    // cy.editWhitelist();
    // cy.deselectAllWhitelist();
    // cy.loginToManageAndSelectTiffanyAching();
    // cy.goToWhitelistingTab();
    // cy.checkAllWhitelistIsUnchecked();
    // cy.selectService(2);
    // cy.verifyCreation();
    // cy.verifyAttributeMotivations(attributesTitles);
  });

  after(() => {
    // cy.selectService(2);
    // cy.deleteEntity();
  });
});
