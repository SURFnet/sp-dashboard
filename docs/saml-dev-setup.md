# Setting up federative login on the development environment

The default application settings (parameters.yml.dist) are tailored to
the development VM - everything including federative login should work
out-of-the-box.

In order to actually login onto the dashboard you must first configure
the openconext components on the VM. Below steps will guide you trough
the process.

## Configure the dashboard as SP in service registry

 1. Visit https://serviceregistry.dev.support.surfconext.nl/
 2. Enter username 'admin' on the mujina IDP login form (empty password)
 3. Click 'Create connection'
 4. Enter connection ID: 'https://spdashboard.dev.support.surfconext.nl/saml/metadata'
 5. Fill out 'Create entity fromURL': https://spdashboard.dev.support.surfconext.nl/saml/metadata'
 6. Set the type to 'SAML 2.0 SP'
 7. Click 'Create'
 8. Then set the state to 'Production'
 9. Repeat steps 4 to 8 with Connection ID and entity url: `https://spdashboard.dev.support.surfconext.nl/app_dev.php/saml/metadata`
 
You should now be able to successfully login!

To get access as admin. Login with Mujinja IdP. Using username: admin password: '' (none). Or use another user that
belongs to the admin team (set in the `parameters.yml` config file defaults to: 'urn:collab:org:surf.nl').