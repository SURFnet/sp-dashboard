function stringTemplateParser(expression, valueObj) {
    const templateMatcher = /{{\s?([^{}\s]*)\s?}}/g;
    let text = expression.replace(templateMatcher, (substring, value, index) => {
        value = valueObj[value];
        return value;
    });
    return text
}

Cypress.Commands.add('createEntity', (entityId, team, name, attributes, environment = 	'testaccepted', entityType = 'oidc10_rp') => {
    let typeDashConverted = entityType.toString().replace('_', '-');
    let metadataTemplate = require('./fixtures/new_oidc_entity_template.json');
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
        type: entityType,
        typeDashConverted: typeDashConverted,
        attributes: attributes,
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
            console.log(metadataTemplate, error);
        });
});

Cypress.Commands.add('removeEntitiesForTeam', (teamName) => {

    const protocols = ['saml20_sp', 'oidc10_rp', 'oauth20_rs'];

    let searchUri = 'https://manage.vm.openconext.org/manage/api/internal/search/${type}'

    const searchBody = '{"metaDataFields.coin:service_team_id": "' + teamName.toString() +'"}';

    for (const index in protocols) {
        const entityType = protocols[index];
        const uri = searchUri.toString().replace('${type}', entityType);
        fetch(uri, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Basic ' + window.btoa("sp-dashboard:secret")
            },
            body: searchBody
        })
        .then(response => response.json())
        .then(data => {
            for (const entityIndex in data) {
                const entityId = data[entityIndex]['_id'];
                const deleteUri = 'https://manage.vm.openconext.org/manage/api/internal/metadata/${type}/${entityId}';
                let uri = deleteUri.toString().replace('${entityId}', entityId);
                uri = uri.toString().replace('${type}', entityType);

                fetch(uri, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Basic ' + window.btoa("sp-dashboard:secret")
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        return true;
                    })
                    .catch(error => {
                        return false;
                    });
            }
        })
        .catch(error => {
            console.log(error);
        });
    }
});

Cypress.Commands.add('deleteEntity', (entityType = '', entityId = '') => {
    const deleteUri = 'https://manage.vm.openconext.org/manage/api/internal/metadata/${type}/${entityId}';
    let uri = deleteUri.toString().replace('${entityId}', entityId);
    uri.toString().replace('${type}', entityType);

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
            return true;
        })
        .catch(error => {
            return false;
        });
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
