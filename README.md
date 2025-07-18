<a href="https://www.surf.nl/over-surf/werkmaatschappijen/surfnet">
    <img src="https://www.surf.nl/themes/surf/logo.svg" alt="SURFnet"
         align="right" />
</a>

[![Build status](https://github.com/SURFnet/sp-dashboard/actions/workflows/test-integration.yml/badge.svg)](https://github.com/SURFnet/sp-dashboard/actions/workflows/test-integration.yml)
[![License](https://img.shields.io/github/license/SURFnet/sp-dashboard.svg)](https://github.com/SURFnet/sp-dashboard/blob/master/LICENSE.txt)

# Service Provider Dashboard

The Service Provider Dashboard is a dashboard application where
[SURFconext](https://www.surf.nl/diensten-en-producten/surfconext/index.html) Service Providers can register and manage
their services. This can be both SAML 2.0, OpenID Connect Relying Parties and Oauth 2.0 Resource Server entities.

## Prerequisites

- [PHP](https://secure.php.net/manual/en/install.php) (8.2)
- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

An entry in your hostsfile is still required for things to work. An example entry would look like:

```
127.0.0.1 welcome.dev.openconext.local static.dev.openconext.local mujina-sp.dev.openconext.local mujina-idp.dev.openconext.local engine-api.dev.openconext.local oidc.dev.openconext.local manage.dev.openconext.local spdashboard.dev.openconext.local engine.dev.openconext.local teams.dev.openconext.local
```

Is your host system on an ARM based archetecture CPU, and are you running a docker solution in a VM? Chances are 
you are not going to be able to step debug with XDebug. To achieve this you will need to use a slightly different
XDebug setting. In order to deliver those settings into the `php-fpm` container we suggest you a
`docker-compose.overrides.yml` file based on the dist file you will find in the docroot. You might need to do some
additional changes to your IDE. [This Jetbrains Blogpost](https://blog.jetbrains.com/phpstorm/2018/08/quickstart-with-docker-in-phpstorm/) 
might aid in that area.

## Getting started

This setup includes the OpenConext environment from the [Devconf](https://github.com/OpenConext/OpenConext-devconf) project. You need to checkout this project in the same directory as the sp-dashboard appcode. You will have:

dir/
  - sp-dashboard/
  - OpenConext-devconf/


To bootstrap the development environment, you'll need a working Devconf setup.
Please follow the instructions in the [OpenConext-devconf README](https://github.com/OpenConext/OpenConext-devconf/blob/main/README.md)


If Devconf is provisioned and up and running then you can start the command line in the container with
`./start-dev-env.sh spdashboard:/home/bas/Projects/surf/sp-dashboard` in the `OpenConext-devconf/core` directory.

Then run the following commands to install the PHP and JS dependencies:

```bash
Run `composer install --ignore-platform-req=ext-zip`. This will install all PHP dependencies, including the development dependencies.
Run `yarn install`. This will install all js dependencies, including the development dependencies.
Run `yarn encore dev`. This will build the frontend.

Install database migrations
```
bin/console doctrine:migrations:migrate --env=dev
```

The application is now up and running and can be accessed at
[https://spdashboard.dev.openconext.local/](https://spdashboard.dev.openconext.local). 

Be aware that you will now log in as admin, you can change that behaviour if you would like to:
See: [Testing the front-end with different priviliges](docs/testing-with-different-priviliges.md)

### Running the tests

`composer check` will run the full suite of tests and static analysis.

Cypress tests only run locally for now.  Use `npm run cy:open` to run them.  Please ensure the accessibility tests succeed when making changes in the frontend.

Some remarks: 

1. The Sp Dashboard sp should be configured with `coin:no_consent_required` = 1. Not having this option set will result in a failing authentication sequence.
2. The Cypress tests should run in your dev environment. No fixture is provided yet to run the tests against.
3. Running tests in PROD mode is encouraged (this disengages the web debug toolbar) saving false positives in a11y and html validation errors
4. A service with ID = 2 should be present, it should have the add production entities option set.

## Other resources

 - [Developer documentation](docs/index.md)
 - [Issue tracker](https://www.pivotaltracker.com/n/projects/1400064)
 - [License](LICENSE.txt)
