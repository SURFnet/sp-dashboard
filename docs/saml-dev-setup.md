# Setting up federative login on the development environment

The default application settings (parameters.yml.dist) are tailored to
the development VM - everything including federative login should work
out-of-the-box.

In order to actually login onto the dashboard you must first configure
the openconext components on the VM. Below steps will guide you trough
the process.

## Configure the dashboard as SP in manage

 1. Login into the guest (vagrant ssh)
 2. Add `127.0.0.1 spdashboard.dev.support.surfconext.nl` to /etc/hosts
 3. Visit `https://manage.dev.support.surfconext.nl/` in your browser
 4. Import the metadata from this URL: `https://spdashboard.dev.support.surfconext.nl/saml/metadata`
 5. In order to be usable in EngineBlock: update the SP entities to have the `prodaccepted` state
 6. Click 'Push metadata'
 7. Repeat steps 4 and 5 for the development-mode: `https://spdashboard.dev.support.surfconext.nl/app_dev.php/saml/metadata`
 
You should now be able to successfully login!

To get access as admin. Login with Mujinja IdP. Using username: admin password: '' (none). Or use another user that
belongs to the admin team (set in the `parameters.yml` config file defaults to: 'urn:collab:org:surf.nl').

## Configure the second Manage instance as SP in Manage

A Second Manage instance is installed when deploying the VM. This instance is meant to simulate the production Manage instace.
It needs to be added as an SP in the other manage instance so that it can be access:

 1. Login to manage (https://manage.dev.support.surfconext.nl)
 2. Go to the manage sp
 3. Go to the export tab and copy the metadata xml or json
 4. Go to the import page via the main menu Import button
 5. Import the metadata you have on your clipboard and update all https://manage.dev.support.surfconext.nl with https://manage-prod.dev.support.surfconext.nl
 6. Make sure that the nameID is set to unspecified
 7. Save the changes, and push to Enineblock

You should now be able to login to https://manage-prod.dev.support.surfconext.nl

