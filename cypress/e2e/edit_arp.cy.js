context('Edit ARP', () => {
  before(() => {
    cy.login();
    cy.createEntity([], 'test', 'https://tiffany.aching.do/arp-id');
  });

  // Als ik een entity (oidcng en saml) ga wijzigen, en er staan onbekende attributen in de ARP in Manage, dan moeten deze blijven staan als ik op publish klik
  it('unknown ARP attributes remain in Manage after an edit', () => {
    cy.loginToManageAndSelectTiffanyAching(undefined, 'https://tiffany.aching.do/arp-id');
    cy.goToArpTab();
    cy.addSurfCrmId();
    cy.visit('https://spdashboard.dev.openconext.local/service/2');
    cy.editEntity('test', 'https://tiffany.aching.do/arp-id');
    cy.addPersonalCodeAttribute();
    cy.addComment('testing editing an attribute');
    cy.clickPublishButton();
    cy.loginToManageAndSelectTiffanyAching(undefined, 'https://tiffany.aching.do/arp-id');
    cy.goToArpTab();
    cy.checkSurfCrmIdIsChecked();
  });

  after(() => {
    cy.deleteEntity('saml20_sp', 'https://tiffany.aching.do/arp-id');
  });
});
