import { defineConfig } from 'cypress';

// @ts-ignore
export default defineConfig({
  e2e: {
    chromeWebSecurity: false,
    experimentalSessionAndOrigin: true,
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config);
    },
  },
});
