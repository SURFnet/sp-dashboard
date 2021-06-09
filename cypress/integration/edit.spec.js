context('Consent verify a11y', () => {
    beforeEach(() => {
        cy.loginToService();
    });

    // Als ingelogde gebruiker van dienst X kan ik geen edits doen in dienst Y (door het raden van de url van een andere service bijvoorbeeld)
    it('is not possible to edit another service', () => {
        cy.visit('https://spdashboard.vm.openconext.org/service/3', { failOnStatusCode: false });
        cy.url().should('not.eq', 'https://spdashboard.vm.openconext.org/service/3')
    });

    // Als ik een entity (oidcng en saml) ga wijzigen, en er staan onbekende attributen in de ARP in Manage, dan moeten deze blijven staan als ik op publish klik
    it('unknown ARP attributes remain in Manage after an edit', () => {

    });

    // Als ik de ACL wijzig van een bestaande entity dan moeten alle andere velden ongemoeid blijven (ik kan een manage export aanleveren met een entity waarin heel veel aanpassingen zijn gedaan eventueel
    it('i can change the ACL of an existing entity without changing the other fields', () => {

    });

    // Als ik een opmerking plaats moet die mee in het comment veld van Manage
    it('when creating an entity, the comment should be in the comment field in Manage', () => {

    });
});
