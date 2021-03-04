<a href="https://www.surf.nl/over-surf/werkmaatschappijen/surfnet">
    <img src="https://www.surf.nl/themes/surf/logo.svg" alt="SURFnet"
         align="right" />
</a>

[![Build status](https://img.shields.io/travis/SURFnet/sp-dashboard.svg)](https://travis-ci.org/SURFnet/sp-dashboard)
[![License](https://img.shields.io/github/license/SURFnet/sp-dashboard.svg)](https://github.com/SURFnet/sp-dashboard/blob/master/LICENSE.txt)

# Service Provider Dashboard

The Service Provider Dashboard is a dashboard application where
[SURFconext](https://www.surf.nl/diensten-en-producten/surfconext/index.html) Service Providers can register and manage
their services. This can be both SAML 2.0, OpenID Connect Relying Parties and Oauth 2.0 Resource Server entities.

## Prerequisites

- [PHP](https://secure.php.net/manual/en/install.php) (7.2)
- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Apache Ant](https://ant.apache.org/manual/install.html)
- [Ansible](https://docs.ansible.com/ansible/intro_installation.html)
- [Vagrant](https://www.vagrantup.com/docs/installation/)
  - Optional, but recommended: [Hostsupdater plugin](https://github.com/cogitatio/vagrant-hostsupdater)
- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

As of release 2.8 the Vagrant + Ansible dev environment has been discontinued in favour of a docker-compose installable
dev machine. Instructions below should still help you in building a Vagrant based dev env, but this will be removed from
the project in the next release. For now use `docker-compose up -d` to create and build the development environment.

An entry in your hostsfile is still required for things to work. An example entry would look like:

```
127.0.0.1 welcome.vm.openconext.org static.vm.openconext.org mujina-sp.vm.openconext.org mujina-idp.vm.openconext.org engine-api.vm.openconext.org oidc.vm.openconext.org manage.vm.openconext.org spdashboard.vm.openconext.org
```


**Deprecation warning!**
The Ansible playbook for SP Dashboard depends on some roles from
[OpenConext-deploy](https://github.com/OpenConext/OpenConext-deploy), so in order to provision the Vagrant box you need
to have that repository checked out in a directory called `OpenConext-deploy` in the parent directory of where this
project lives.

## Provision the VM

**Deprecation warning!** Try the Docker dev env! 
The VM is provisioned using Ansible and Vagrant. After you have installed those, you can run
```
vagrant up
```

Grab some coffee or two, and your VM will be ready.

If you need to run specific roles on the VM, you can use the corresponding tag. The tags can be found in the playbook (ansible/vagrant.yml)
The tags need to in the environment variable "ANSIBLE_TAGS", seperated by a ,. So if you want to provision the tags eb and profile, you'd run:
```
ANSIBLE_TAGS=eb,profile vagrant provision
```

## Getting started

First, run `composer install`. This will install all PHP dependencies, including the development dependencies.

In order to start the development environment, run `docker-compose up -d`. This will build and start the container that is
used in development to run the application. 

Install database migrations
```
$ docker exec sp-dashboard_php-fpm_1  /var/www/html/bin/console doctrine:migrations:migrate
```

The application is now up and running and can be accessed at
[https://spdashboard.vm.openconext.org/](https://spdashboard.vm.openconext.org). Note that in development the `app_dev.php`
front controller is used automatically, so you don't have to include `/app_dev.php/` in the URLs.
* To view mails caught by Mailcatcher, visit [spdashboard.vm.openconext.org:1080](https://spdashboard.vm.openconext.org:1080/)

### Running the tests

`ant test` will run the full suite of tests and static analysis.

### Xdebug
Xdebug is configured when provisioning your development Vagrant box. The Vagrantfile sets the `develop_spd` 
environment variable in Ansible. This will prevent Xdebug from being enabled in test and production releases.

## Other resources

 - [SAML configuration for development](docs/saml-dev-setup.md)
 - [Developer documentation](docs/index.md)
 - [Issue tracker](https://www.pivotaltracker.com/n/projects/1400064)
 - [License](LICENSE.txt)
