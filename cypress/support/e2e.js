import './commands';
import './commands/atttributes';
import './commands/createEntity';
import './commands/editEntity';
import './commands/entityActions';
import './commands/login';
import './commands/manage';

import 'cypress-axe';
import 'cypress-html-validate/dist/commands';

// Disable CSS animations/transitions globally so axe and other checks
// don't fail due to elements being mid-animation (e.g. opacity: 0 fade-ins).
Cypress.on('window:load', (win) => {
    const style = win.document.createElement('style');
    style.textContent = '*, *::before, *::after { animation-duration: 0s !important; animation-delay: 0s !important; transition-duration: 0s !important; transition-delay: 0s !important; }';
    win.document.head.appendChild(style);
});

// cypress-axe uses a hardcoded relative path that resolves incorrectly when
// running with --config-file pointing to ci/qa/. Override to use the correct path.
Cypress.Commands.overwrite('injectAxe', () => {
    cy.readFile('node_modules/axe-core/axe.min.js').then((source) => {
        cy.window({ log: false }).then((win) => {
            win.eval(source);
        });
    });
});

Cypress.on('uncaught:exception', (err, runnable) => {
    // we expect a parsley library error with message 'parsley'
    // and don't want to fail the test so we return false
    if (err.message.includes('parsley')) {
        return false;
    }
    // Ignore React focus errors from the Manage application
    if (err.message.includes("Cannot read properties of null (reading 'focus')")) {
        return false;
    }
    // Ignore JSON parse errors from Manage React app (unhandled promise rejections)
    if (err.message.includes('Unexpected end of JSON input') || err.message.includes('Unexpected token')) {
        return false;
    }
    // we still want to ensure there are no other unexpected
    // errors, so we let them fail the test
})
