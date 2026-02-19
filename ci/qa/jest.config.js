// Auto-detect if running in Docker or local development
const fs = require('fs');
const isDocker = fs.existsSync('/var/www/html/composer.json');
const rootDir = isDocker ? '/var/www/html' : '../../';

module.exports = {
    rootDir: rootDir,
    collectCoverage: false,
    coverageDirectory: "./assets/js/coverage",
    collectCoverageFrom: [
        "./assets/js/**/*.{ts,tsx,js,jsx}",
        "!./public/build/**",
        "!./**/*test.{ts,tsx,js,jsx}",
        "!./node_modules/**"
    ],
    coverageThreshold: {
        "global": {
            "branches": 100,
            "functions": 100,
            "lines": 100,
            "statements": 0
        }
    },
    moduleFileExtensions: [
        "ts",
        "tsx",
        "js",
        "jsx",
        "json"
    ],
    modulePathIgnorePatterns: [
        "\\.snap$",
        "<rootDir>/node_modules",
        "<rootDir>/public/build"
    ],
    transform: {
        "\\.(ts|tsx)$": ["ts-jest", {
            tsconfig: "tsconfig.jest.json",
            diagnostics: {
                ignoreCodes: [151001, 2349, 2351]
            },
        }]
    },
    testRegex: ".*\\.test\\.(ts|tsx|js|jsx)$",
    testEnvironment: "jsdom",
    setupFiles: [
        "jest-canvas-mock",
        "<rootDir>/ci/qa/jest.setup.js"
    ]
};
