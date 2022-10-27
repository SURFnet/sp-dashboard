function stringTemplateParser(expression, valueObj) {
    const templateMatcher = /{{\s?([^{}\s]*)\s?}}/g;
    let text = expression.replace(templateMatcher, (substring, value, index) => {
        value = valueObj[value];
        return value;
    });
    return text
}

Cypress.Commands.add('createEntity',
    (
        entityId,
        team,
        name,
        attributes,
        environment = 'testaccepted',
        excludeFromPush = 1,
        type = 'oidc10_rp'
    ) => {
    let metadataTemplate;
    if (type === 'oidc10_rp') {
        metadataTemplate = require('./fixtures/new_oidc_entity_template.json');
    } else {
        metadataTemplate = require('./fixtures/new_saml_entity_template.json');
    }
    if (attributes) {
        let arp = JSON.parse('{"arp": {"attributes": {},"enabled": true}}');
        let arpAttributes = [];
        for (const [urn, motivation] of Object.entries(attributes)) {
            let attributeObject = {
                urn: [{
                    "source": "idp",
                    "value": "*",
                    "motivation": motivation
                }]
            }
            arpAttributes.push(attributeObject);
        }
        arp.attributes = arpAttributes;
    }
    const templateVariables = {
        name: name,
        entityId: entityId,
        environment: environment,
        team: team,
        attributes: attributes,
        excludeFromPush: excludeFromPush
    };
    metadataTemplate = stringTemplateParser(JSON.stringify(metadataTemplate), templateVariables);

    fetch('https://manage.vm.openconext.org/manage/api/internal/metadata', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Basic ' + window.btoa("sp-dashboard:secret")
        },
        body: metadataTemplate
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.log(error);
        });
});

Cypress.Commands.add('deleteEntity', (entityId, type) => {
    let deleteUri = `https://manage.vm.openconext.org/manage/api/internal/metadata/${type}/${entityId}`;
    fetch(deleteUri, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Basic ' + window.btoa("sp-dashboard:secret")
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.log(error);
        });
});

Cypress.Commands.add('removeEntitiesForTeam', (teamName) => {

    // const protocols = ['saml20_sp', 'oidc10_rp', 'oauth20_rs'];
    //
    // let searchUri = 'https://manage.vm.openconext.org/manage/api/internal/search/${type}'
    //
    // fetch(searchUri, {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'Accept': 'application/json',
    //         'Authorization': 'Basic ' + window.btoa("sp-dashboard:secret")
    //     },
    //     body
    // })
    //     .then(response => response.json())
    //     .then(data => {
    //         console.log(data);
    //     })
    //     .catch(error => {
    //         console.log(error);
    //     });
    //
   //cy.deleteEntity();
});

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
