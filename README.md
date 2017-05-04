<a href="https://www.surf.nl/over-surf/werkmaatschappijen/surfnet">
    <img src="https://www.surf.nl/binaries/werkmaatschappijlogo/content/gallery/surf/logos/surfnet.png" alt="SURFnet"
         align="right" />
</a>

[![Build status](https://img.shields.io/travis/SURFnet/sp-dashboard.svg)](https://travis-ci.org/SURFnet/sp-dashboard)
[![License](https://img.shields.io/github/license/SURFnet/sp-dashboard.svg)](https://github.com/SURFnet/sp-dashboard/blob/master/LICENSE.txt)

# Service Provider Dashboard

The Service Provider Dashboard is a dashboard application where
[SURFconext](https://www.surf.nl/diensten-en-producten/surfconext/index.html) Service Providers can register and manage
their services.

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

## Getting started

First, run `composer install`. This will install all PHP dependencies, including the development dependencies.

In order to start the development environment, run `vagrant up`. This will build and start the virtual machine that is
used in development to run the application. When started for the first time, the Vagrant box will be provisioned using
Ansible.

Note: if you don't use the Vagrant Hostsupdater plugin, you have to manually add
`192.168.33.19  dev.support.surfconext.nl` to your hosts file so that requests will be routed to the virtual machine.

The application is now up and running and can be accessed at
[https://dev.support.surfconext.nl/](https://dev.support.surfconext.nl/). Note that in development the `app_dev.php`
front controller is used automatically, so you don't have to include `/app_dev.php/` in the URLs.

### Running the tests

`ant test` will run the full suite of tests and static analysis.

## Other resources

 - [Developer documentation](docs/index.md)
 - [Issue tracker](https://www.pivotaltracker.com/n/projects/1400064)
 - [License](LICENSE.txt)
