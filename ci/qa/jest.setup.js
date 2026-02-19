// Jest setup file - runs before each test file
// Mock jQuery globally to prevent errors when modules call $(document).ready() at import time

const $ = require('jquery');
global.$ = $;
global.jQuery = $;

// Mock window.$ for tests that expect it
if (typeof window !== 'undefined') {
  window.$ = $;
  window.jQuery = $;
}
