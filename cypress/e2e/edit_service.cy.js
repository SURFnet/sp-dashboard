import {attributes} from '../fixtures/variables';

context('Edit another service', () => {
    before(() => {
        cy.login();
        cy.createEntity(attributes);
    });

    // Als ingelogde gebruiker van dienst X kan ik geen edits doen in dienst Y (door het raden van de url van een andere service bijvoorbeeld)
    it('is not possible to edit another service', () => {
        cy.visit('https://spdashboard.dev.openconext.local/service/99999', { failOnStatusCode: false });
        cy.contains('Unable to find service');
    });

    after(() => {
        cy.deleteEntity();
    });
});
