const MANAGE_URL = 'https://manage.dev.openconext.local';
const MANAGE_HEADERS = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': 'Basic ' + btoa('sp-dashboard:secret'),
};

function stringTemplateParser(expression, valueObj) {
    const templateMatcher = /{{\s?([^{}\s]*)\s?}}/g;
    let text = expression.replace(templateMatcher, (substring, value, index) => {
        value = valueObj[value];
        return value;
    });
    return text
}

function manageSearchAndDelete(entityType, searchBody) {
    cy.request({
        method: 'POST',
        url: `${MANAGE_URL}/manage/api/internal/search/${entityType}`,
        headers: MANAGE_HEADERS,
        body: searchBody,
        failOnStatusCode: false,
    }).then(({ body }) => {
        (body || []).forEach(entity =>
            cy.request({
                method: 'DELETE',
                url: `${MANAGE_URL}/manage/api/internal/metadata/${entityType}/${entity._id}`,
                headers: MANAGE_HEADERS,
                failOnStatusCode: false,
            })
        );
    });
}

Cypress.Commands.add('createEntityViaManageApi', (entityId, team, name, attributes, environment = 'testaccepted', serviceIdOrEntityType = 'oidc10_rp', entityType = null) => {
    // Support both 6-arg (no serviceId) and 7-arg (with serviceId) calling conventions
    const resolvedEntityType = entityType || (typeof serviceIdOrEntityType === 'string' ? serviceIdOrEntityType : 'oidc10_rp');
    let typeDashConverted = resolvedEntityType.toString().replace('_', '-');
    let metadataTemplate = resolvedEntityType === 'saml20_sp'
        ? require('./fixtures/new_saml_entity_template.json')
        : require('./fixtures/new_oidc_entity_template.json');
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
        type: resolvedEntityType,
        typeDashConverted: typeDashConverted,
        attributes: attributes,
        excludeFromPush: '1',
    };
    metadataTemplate = stringTemplateParser(JSON.stringify(metadataTemplate), templateVariables);

    cy.request({
        method: 'POST',
        url: `${MANAGE_URL}/manage/api/internal/metadata`,
        headers: MANAGE_HEADERS,
        body: JSON.parse(metadataTemplate),
        failOnStatusCode: false,
    });
});

Cypress.Commands.add('removeEntitiesForTeam', (teamName) => {
    ['saml20_sp', 'oidc10_rp', 'oauth20_rs'].forEach((type) =>
        manageSearchAndDelete(type, { 'metaDataFields.coin:service_team_id': teamName })
    );
});

Cypress.Commands.add('deleteEntity', (entityType = 'saml20_sp', entityId = 'https://tiffany.aching.do/id') => {
    if (!entityType || !entityId) return;
    manageSearchAndDelete(entityType, { entityid: entityId });
});

Cypress.Commands.add('loginToManage', (url = MANAGE_URL) => {
    cy.visit(url);
    cy.wait(300);
    cy.checkForManage();
});

Cypress.Commands.add('loginToManageAndSelectTiffanyAching', (url = MANAGE_URL, entityId = 'https://tiffany.aching.do/id') => {
    cy.loginToManage(url);
    cy.request({
        method: 'POST',
        url: `${MANAGE_URL}/manage/api/internal/search/saml20_sp`,
        headers: MANAGE_HEADERS,
        body: { entityid: entityId },
        failOnStatusCode: false,
    }).then((response) => {
        const entity = (response.body || [])[0];
        cy.log(`loginToManageAndSelectTiffanyAching: entityId=${entityId} found=${!!entity} count=${(response.body||[]).length}`);
        if (entity) {
            cy.visit(`${url}/metadata/saml20_sp/${entity._id}/connection`);
        }
    });
});

Cypress.Commands.add('checkForManage', (tries = 0) => {
    cy.get('body').then((body) => {
        const isLoginPage = body.find('.login-form').length;
        const isManagePage = body.find('.search-input').length;
        if (isLoginPage) {
            cy.get('#username').type('Tiffany');
            cy.get('#password').type('Aching');
            cy.get('.login-form').submit();
        }

        if (!isManagePage && tries < 30) {
            cy.checkForConsent();
            cy.wait(1000);
            cy.checkForManage(++tries);
        }
    });
});

Cypress.Commands.add('goToArpTab', () => {
    cy.contains('ARP', {timeout: 10000}).click();
});

Cypress.Commands.add('goToWhitelistingTab', () => {
    cy.contains('Whitelisting', {timeout: 10000}).click();
});

Cypress.Commands.add('goToMetadataTab', () => {
    cy.contains('Metadata', {timeout: 10000}).click();
});

Cypress.Commands.add('addSurfCrmId', (note = 'add surf crm id because it\'s not supported', motivation = 'we wants it') => {
    cy.get('label[for="urn:mace:surf.nl:attribute-def:surf-crm-id"]').click();
    cy.get('input[name="urn:mace:surf.nl:attribute-def:surf-crm-id"]')
        .closest('tbody')
        .find('input[placeholder="Motivation..."]')
        .type(motivation);
    cy.addRevisionNote(note);
    cy.get('.actions .buttons .button.blue').click({force: true});
    cy.wait(1000);
});

Cypress.Commands.add('addRevisionNote', (note = 'a note') => {
    cy.get('input[name="revisionnote"]').clear({force: true}).type(note, {force: true});
});

Cypress.Commands.add('checkRevisionNote', (note = 'ya always know just what ta say, don\'tcha?') => {
    cy.contains('Revisions').click();
    cy.get('.revision-table tbody tr').first().find('td').eq(4).should('contain.text', note);
});

Cypress.Commands.add('checkSurfCrmIdIsChecked', () => {
    cy.get('input[name="urn:mace:surf.nl:attribute-def:surf-crm-id_*_0"]').should('be.checked');
});

Cypress.Commands.add('checkExcludeFromPushIsChecked', () => {
    cy.url().then((url) => {
        const match = url.match(/\/metadata\/([^/]+)\/([^/]+)/);
        if (match) {
            cy.request({
                method: 'GET',
                url: `${MANAGE_URL}/manage/api/internal/metadata/${match[1]}/${match[2]}`,
                headers: MANAGE_HEADERS,
            }).then((resp) => {
                const excludeFromPush = resp.body.data.metaDataFields['coin:exclude_from_push'];
                expect(excludeFromPush == '1' || excludeFromPush === true).to.equal(true);
            });
        }
    });
});

Cypress.Commands.add('checkAllWhitelistIsUnchecked', () => {
    cy.get('#allow-all').should('not.be.checked');
});

Cypress.Commands.add('checkAllWhitelistIsChecked', () => {
    cy.get('#allow-all').should('be.checked');
});

Cypress.Commands.add('deleteOnManage', () => {
    cy.url().then((url) => {
        const match = url.match(/\/metadata\/([^/]+)\/([^/]+)/);
        if (match) {
            cy.request({
                method: 'DELETE',
                url: `${MANAGE_URL}/manage/api/internal/metadata/${match[1]}/${match[2]}`,
                headers: MANAGE_HEADERS,
                failOnStatusCode: false,
            });
        }
    });
});
