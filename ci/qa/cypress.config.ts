import { defineConfig } from 'cypress';

// @ts-ignore
export default defineConfig({
  e2e: {
    chromeWebSecurity: false,
    screenshotOnRunFailure: false,
    video: false,
    // Disabled so the browser session (SAML auth cookie) persists across specs.
    // Enabling this would force a full browser reset between each test, breaking the login flow.
    testIsolation: false,
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('../../cypress/plugins/index.js')(on, config);
    },
  },
});
