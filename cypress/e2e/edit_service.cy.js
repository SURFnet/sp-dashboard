import {attributes} from '../fixtures/variables';

context('Edit another service', () => {
    before(() => {
        cy.login();
        cy.selectService(1);
        cy.contains('New production entity').first().click();
        cy.createEntity(attributes);
    });

    // Als ingelogde gebruiker van dienst X kan ik geen edits doen in dienst Y (door het raden van de url van een andere service bijvoorbeeld)
    it('is not possible to edit another service', () => {
        cy.visit('https://spdashboard.vm.openconext.org/service/3', { failOnStatusCode: false });
        cy.contains('Unable to find service');
    });

    after(() => {
        cy.deleteEntity();
    });
});
