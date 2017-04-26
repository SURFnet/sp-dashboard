<a href="https://www.surf.nl/over-surf/werkmaatschappijen/surfnet">
    <img src="https://www.surf.nl/binaries/werkmaatschappijlogo/content/gallery/surf/logos/surfnet.png" alt="SURFnet"
         align="right" />
</a>

[![Build status](https://img.shields.io/travis/SURFnet/sp-dashboard.svg)](https://travis-ci.org/SURFnet/sp-dashboard)

# Service Provider Dashboard

Dashboard for [SURFconext](https://www.surf.nl/diensten-en-producten/surfconext/index.html) Service Providers.

## Prerequisites

- [Composer](https://getcomposer.org/doc/00-intro.md)
- [Ansible](https://docs.ansible.com/ansible/intro_installation.html)
- [Vagrant](https://www.vagrantup.com/docs/installation/)
  - Optional, but recommended: [Hostsupdater plugin](https://github.com/cogitatio/vagrant-hostsupdater)

## Getting started

Install Composer dependencies using:

```bash
composer install
```

Start the Vagrant box:

```bash
vagrant up
```

The web interface is now accessible at [https://dev.support.surfconext.nl/](https://dev.support.surfconext.nl/).
Note: if you don't use the Vagrant Hostsupdater plugin, you have to manually add `dev.support.openconext.nl` to your hosts file.
