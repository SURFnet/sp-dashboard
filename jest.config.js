module.exports = {
    collectCoverage: false,
    coverageDirectory: "app/js/coverage",
    collectCoverageFrom: [
        "app/js/**/*.{ts,tsx,js,jsx}",
        "!web/build/**",
        "!**/*test.{ts,tsx,js,jsx}",
        "!node_modules/**"
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
        "<rootDir>/web/build"
    ],
    transform: {
        "\\.(ts|tsx)$": "ts-jest"
    },
    testRegex: ".*\\.test\\.(ts|tsx|js|jsx)$",
    globals: {
        "ts-jest": {
            tsConfig: "tsconfig.json",
        }
    },
    "setupFiles": [
        "jest-canvas-mock",
    ]
};