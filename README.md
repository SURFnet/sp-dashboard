<a href="https://www.surf.nl/over-surf/werkmaatschappijen/surfnet">
    <img src="https://www.surf.nl/themes/surf/logo.svg" alt="SURFnet"
         align="right" />
</a>

[![Build status](https://img.shields.io/travis/SURFnet/sp-dashboard.svg)](https://travis-ci.org/SURFnet/sp-dashboard)
[![License](https://img.shields.io/github/license/SURFnet/sp-dashboard.svg)](https://github.com/SURFnet/sp-dashboard/blob/master/LICENSE.txt)

# Service Provider Dashboard

The Service Provider Dashboard is a dashboard application where
[SURFconext](https://www.surf.nl/diensten-en-producten/surfconext/index.html) Service Providers can register and manage
their services. This can be both SAML 2.0 and OpenID Connect entities.

## Prerequisites

- [PHP](https://secure.php.net/manual/en/install.php) (5.6 or higher)
- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Apache Ant](https://ant.apache.org/manual/install.html)
- [Ansible](https://docs.ansible.com/ansible/intro_installation.html)
- [Vagrant](https://www.vagrantup.com/docs/installation/)
  - Optional, but recommended: [Hostsupdater plugin](https://github.com/cogitatio/vagrant-hostsupdater)

The Ansible playbook for SP Dashboard depends on some roles from
[OpenConext-deploy](https://github.com/OpenConext/OpenConext-deploy), so in order to provision the Vagrant box you need
to have that repository checked out in a directory called `OpenConext-deploy` in the parent directory of where this
project lives.

## Provision the VM

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

In order to start the development environment, run `vagrant up`. This will build and start the virtual machine that is
used in development to run the application. When started for the first time, the Vagrant box will be provisioned using
Ansible.

Note: if you don't use the Vagrant Hostsupdater plugin, you have to manually add
`192.168.33.19  dev.support.surfconext.nl` to your hosts file so that requests will be routed to the virtual machine.

Install database migrations
```
bin/console doctrine:migrations:migrate
```

Now follow the instructions in [SAML configuration for development](docs/saml-dev-setup.md) to setup authentication.
 
The application is now up and running and can be accessed at
[https://spdashboard.dev.support.surfconext.nl/](https://spdashboard.dev.support.surfconext.nl/). Note that in development the `app_dev.php`
front controller is used automatically, so you don't have to include `/app_dev.php/` in the URLs.
* To view mails caught by Mailcatcher, visit [spdashboard.dev.support.surfconext.nl:1080](https://spdashboard.dev.support.surfconext.nl:1080/)

If you run into the `shibsp::ConfigurationException`, please reload your box, the issue should be resolved after a 
reboot. The Shiboleth deamon might not come out 100% correctly out of the initial provisioning run.

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
