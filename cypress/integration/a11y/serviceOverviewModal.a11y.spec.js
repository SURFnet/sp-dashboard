import {terminalLog} from '../../functions/terminalLog';

context('SPD verify a11y of service overview modals', () => {
    beforeEach(() => {
        cy.loginToServiceWithModal();
    });

    it('contains no a11y problems on load', () => {
        cy.injectAxe();
        cy.checkA11y(null, null, terminalLog);
    });

    it('contains no html errors', () => {
        cy.wait(300).then(() => {
            cy.removeSFToolbar();
            cy.htmlvalidate();
        });
    });
});
