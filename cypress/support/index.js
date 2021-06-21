// ***********************************************************
// This is a great place to put global configuration and
// behavior that modifies Cypress.
// ***********************************************************

import './commands';
import './commands/atttributes';
import './commands/createEntity';
import './commands/entityActions';
import './commands/login';
import './commands/manage';

import 'cypress-axe';
import 'cypress-html-validate/dist/commands';

Cypress.on('uncaught:exception', (err, runnable) => {
    // we expect a parsley library error with message 'parsley'
    // and don't want to fail the test so we return false
    if (err.message.includes('parsley')) {
        return false;
    }
    // we still want to ensure there are no other unexpected
    // errors, so we let them fail the test
})
