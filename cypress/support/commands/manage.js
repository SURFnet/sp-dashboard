Cypress.Commands.add('loginToManage', (url = 'https://manage.vm.openconext.org') => {
    cy.visit(url);
    cy.wait(300);
    cy.checkForManage();
});

Cypress.Commands.add('loginToManageAndSelectTiffanyAching', (url = 'https://manage.vm.openconext.org') => {
    cy.loginToManage(url);
    cy.get('.search-input').type('Tiffany Aching');
    cy.get('.matched').contains('Tiffany Aching').click();
});

Cypress.Commands.add('checkForManage', (tries = 0) => {
    cy.get('body').then((body) => {
        const isLoginPage = body.find('.login-form').length;
        const isManagePage = body.find('.search-input').length;
        if (isLoginPage) {
            cy.fillUsername();
            cy.fillPassword();
            cy.get('.login-form').submit();
        }

        if (!isManagePage && tries < 20) {
            cy.checkForConsent();
            cy.wait(300);
            cy.checkForManage(++tries);
        }
    });
});

Cypress.Commands.add('goToArpTab', () => {
    cy.contains('ARP').click();
});

Cypress.Commands.add('goToWhitelistingTab', () => {
    cy.contains('Whitelisting').click();
});

Cypress.Commands.add('goToMetadataTab', () => {
    cy.contains('Metadata').click();
});

Cypress.Commands.add('addSurfCrmId', (note = 'add surf crm id because it\'s not supported', motivation = 'we wants it') => {
    cy.get('label[for="urn:mace:surf.nl:attribute-def:surf-crm-id"]').click();
    cy.focused().type(motivation);
    cy.addRevisionNote(note);
    cy.get('.actions .buttons .button.blue').then((button) => {
        button.trigger('click');
    });
});

Cypress.Commands.add('addRevisionNote', (note = 'a note') => {
    cy.get('input[name="revisionnote"]').then((input) => {
        input.val(note);
    });
});

Cypress.Commands.add('checkRevisionNote', (note = 'ya always know just what ta say, don\'tcha?') => {
    cy.contains('Revision notes').next().contains(note);
});

Cypress.Commands.add('checkSurfCrmIdIsChecked', () => {
    cy.get('#urn:mace:surf.nl:attribute-def:surf-crm-id_*_0').should('be.checked');
});

Cypress.Commands.add('checkExcludeFromPushIsChecked', () => {
    cy.goToMetadataTab();
    cy.get('input[name="coin:exclude_from_push"]').should('be.checked');
});

Cypress.Commands.add('checkAllWhitelistIsUnchecked', () => {
    cy.get('#allow-all').should('not.be.checked');
});

Cypress.Commands.add('checkAllWhitelistIsChecked', () => {
    cy.get('#allow-all').should('be.checked');
});

Cypress.Commands.add('deleteOnManage', () => {
    cy.get('.top-detail a.delete-metadata').click();
    cy.get('.confirmation-dialog-content').contains('Confirm').click();
});
