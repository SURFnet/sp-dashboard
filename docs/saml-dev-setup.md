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
