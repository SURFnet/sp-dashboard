# Frontend tooling

This project uses webpack-encore for asset management.

See: https://symfony.com/blog/introducing-webpack-encore-for-asset-management

The required tooling (nodejs, npm, yarn) is not yet provisioned on the VM automatically. To setup the VM, first install the tools on the VM:

    # yum install npm
    # npm install -g yarn

Then install the project dependencies:

    sp-dashboard $ yarn install

Once this is setup, compile the assets as described by the [http://symfony.com/doc/current/frontend.html](documentation) of webpack-encore:

    yarn run encore dev
    yarn run encore dev -- --watch
    yarn run encore production
