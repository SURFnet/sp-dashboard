context('Connection request e2e tests', () => {

  before(() => {
    cy.createEntity(
        'https://connection-request.openconext.org',
        'urn:collab:group:vm.openconext.org:demo:openconext:org:ffffff',
        'Foobar Service',
        {'attr1': 'foobar', 'attr2': 'bar'},
        'prodaccepted',
        0,
        'saml20_sp'
    );
  });

  beforeEach(() => {
    cy.login();
    cy.selectService(2);
  });

  it('the entity action for connection requests is on the page', () => {
    // The first entity in the entity list should be the published production saml entity we created in the
    // before step.
    cy.get('.production-entities tr').eq(1).get('td.actions li').should('contain.text', 'Create connection request');
  });

  it('the connection request page can be opened', () => {
    cy.openConnectionRequest('https://connection-request.openconext.org');
    cy.get('.page-container h1').should('contain.text', 'Create connection request');
  });

  it('a connection request can be added', () => {
    cy.openConnectionRequest('https://connection-request.openconext.org');
    cy.fillConnectionRequestForm();

    let connectionRequest = cy.get('.collection-list li').first();
    connectionRequest.should('contain.text', 'Harderwijk University');
    connectionRequest.should('contain.text', 'Johny Walker');
    connectionRequest.should('contain.text', 'jay-dob@harderwijk.nl');

    cy.get('#connection_request_container_send').click();
    cy.get('.flashMessage.info')
        .should('contain.text', 'Your connection request were successfully sent');
  });

  it('checks rudimentary validation rules', () => {
    cy.openConnectionRequest('https://connection-request.openconext.org');
    cy.fillConnectionRequestForm();
    // Click the plus button again, attempting adding a duplicate entry
    cy.get('.add_collection_entry').click();

    cy.get('.base-form .error-message')
        .should('contain.text', 'This institution is already requested to be connected.');

    // Okay, we'll fix the issue with the Institution
    cy.get('#connection_request_container_connectionRequests___name___institution')
        .clear()
        .type('Hogeschool Zeeland');
    cy.get('.add_collection_entry').click();

    cy.get('.base-form .error-message')
        .should('not.be.null');

    cy.get('#connection_request_container_connectionRequests___name___institution')
        .clear()
        .type('UVA');

    // Type an invalid email address
    cy.get('#connection_request_container_connectionRequests___name___email')
        .clear()
        .type('invalid@example');

    cy.get('.add_collection_entry').click();

    // Now submit the form
    cy.get('#connection_request_container_send').click();
    cy.get('.parsley-errors-list.filled')
        .should('contain.text', 'This value should be a valid email.');
  });

  it('also performs parsley validation checks on the institution and email fields', () => {
    cy.openConnectionRequest('https://connection-request.openconext.org');

    // Clicking the plus button without filling any items resutls in 2 parsley errors
    cy.get('.add_collection_entry').click();
    cy.get('.parsley-required').should('have.length', 2);

    // After filling the institution name, clicking + results in an error on the email field
    cy.get('#connection_request_container_connectionRequests___name___institution')
        .type('Hogeschool Zeeland');

    cy.get('.add_collection_entry').click();
    cy.get('.parsley-required').should('have.length', 1);
    cy.get('#connection_request_container_connectionRequests___name___email').should('have.focus');
  })

  after(() => {
    cy.deleteEntity('https://connection-request.openconext.org', 'saml20_sp');
  });
});
