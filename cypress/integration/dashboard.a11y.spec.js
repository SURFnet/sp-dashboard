import {terminalLog} from '../functions/terminalLog';

context('Consent verify a11y', () => {
    beforeEach(() => {
        cy.loginWithMemberRole();
    });

    it('contains no a11y problems on load', () => {
        cy.injectAxe();
        cy.checkA11y(null, null, terminalLog);
    });

    it.only('contains no html errors', () => {
        cy.wait(300).then(() => {
            cy.removeSFToolbar();
            cy.htmlvalidate();
        });
    });
});
