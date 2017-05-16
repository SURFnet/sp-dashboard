# Testing

Because of the chosen [architecture](architecture.md) we have the ability to test different parts of the code in
isolation. This allows for a test suite that runs very fast, and can therefore be run very often, keeping the feedback
loop as short as possible. The test suite can also be executed locally as well as on a continuous integration server,
which also helps the developer to get feedback as quickly as possible.

## Continuous integration

Using Travis CI, the full test suite will be run against every pull request and has to pass before it can be merged.
Every commit on the master branch will be tested as well.

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

## Acceptance testing

Acceptance tests test the actual features that the software delivers. It does so by running the application code
(command handlers) in combination with the domain code. The infrastructure (databases, external systems) are replaced
with stand-ins in order to make the test suite faster.

Acceptance tests will be written for all command handlers and will be run as part of every CI build.

Tools: Behat

## Security testing

Using an automated tool, the list of dependencies of the project is being checked against a database of known
vulnerabilities as part of every CI build. If any of the dependencies contains a known vulnerability, the build will
fail.

Tools: SensioLabs Security Checker
