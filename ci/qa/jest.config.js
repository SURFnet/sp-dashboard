module.exports = {
    rootDir: "/var/www/html",
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
        "\\.(ts|tsx)$": "ts-jest"
    },
    testRegex: ".*\\.test\\.(ts|tsx|js|jsx)$",
    globals: {
        "ts-jest": {
            tsconfig: "tsconfig.json",
            diagnostics: {
                ignoreCodes: [151001]
            },
        }
    },
    "setupFiles": [
        "jest-canvas-mock",
    ]
};
