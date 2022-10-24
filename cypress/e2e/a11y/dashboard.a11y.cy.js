import {terminalLog} from '../../functions/terminalLog';

context('SPD verify a11y of dashboard', () => {
    beforeEach(() => {
        cy.login();
        cy.visit('https://spdashboard.vm.openconext.org');
    });

    it('contains no a11y problems on load', () => {
        cy.removeSFToolbar();
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
