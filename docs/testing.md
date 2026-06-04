# Testing

Because of the chosen [architecture](architecture.md) we have the ability to test different parts of the code in
isolation. This allows for a test suite that runs very fast, and can therefore be run very often, keeping the feedback
loop as short as possible. The test suite can also be executed locally as well as on a continuous integration server,
which also helps the developer to get feedback as quickly as possible.

## Continuous integration

Using GitHub Actions, the full test suite will be run against every pull request and has to pass before it can be merged.
Every commit on the main branch will be tested as well.

## Static analysis

Before the tests are run, a number of tools will be used to analyse the source code and detect issues that should be
fixed:

 - A PHP linter checks the PHP code for syntax errors that will prevent the code from being interpreted. This includes
test code as well as production code;
 - The Twig templates and YAML files are checked for syntax errors as well;
 - The composer.json and composer.lock files are being validated;
 - PHP Mess Detector checks a number of metrics, and if they exceed a certain treshold the build will fail;
 - PHP CodeSniffer ensures that the code adheres to the chosen coding standard (PSR-2);
 - PHP Copy-Paste Detector ensures that there is no substantial duplication within the source code.
 - PHPStan performs static analysis to find type errors and other bugs without running the code.
 - A JSON schema validator ensures that the JSON-schema is valid and validates the JSON-data against the JSON-schema.
 
If any of those tools fails, the build will fail and the issue has to be resolved before a pull request can be merged.

## Unit testing

Unit testing verifies that the individual units of source code are working properly, by isolating them from the rest of
the application. A unit is the smallest testable part of code, typically a class. The purpose of unit testing is to
provide design feedback during the development phase, to ensure that code conforms to specifications and to ensure that
future modifications do not introduce unintended side effects.

Unit tests will be written for domain and application code, and will be run as part of every CI build.

Tools: PHPUnit, Mockery

## Integration testing

Integration tests are written specifically for classes that integrate an external system or third-party package. They
cover both the class as well as the external system/package. The purpose of integration testing is to catch incorrect
assumptions about the third-party code and to find any bugs in the third-party code that have to be worked around. It
also catches any issues that arise from updating the external package.

Integration tests will be written for code that integrates with third-party code, which will be in the infrastructure
layer most of the time but might also occur in the application layer (for instance a command validator which integrates
with the Symfony validation component). Controllers will not be covered by integration tests, however. The integration
tests will be run as part of every CI build.

Tools: PHPUnit

## Web testing

Web tests test the application end-to-end through a real browser. They boot a Symfony test server and drive Chrome
via Symfony Panther, exercising the full stack including the database. The purpose of web testing is to verify
that the application works correctly from a user's perspective and to catch regressions in controller, form, and
UI behaviour.

Web tests will be run as part of every CI build.

Tools: PHPUnit, Symfony Panther

## End-to-end testing

End-to-end tests cover key user workflows through the running application, including accessibility checks. These tests
run against the full Docker environment.

Tools: Cypress

## Security testing

Using an automated tool, the list of dependencies of the project is being checked against a database of known
vulnerabilities as a scheduled daily CI job. If any of the dependencies contains a known vulnerability, the build will
fail.

Tools: symfonycorp/security-checker-action, yarn audit

## Code coverage
By default code coverage reports are generated for unit, integration, and webtests suites using the `phpcov` script
in `ci/qa/`. Coverage data files are written to `/tmp/sp-dashboard-coverage/`.

Code coverage can be viewed at `web/coverage/index.html`.

Tests can be run without coverage by using do so by providing the `-no-coverage` suffix. For example when you want to
run the unit tests without coverage use: `phpunit-no-coverage`