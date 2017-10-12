# Setting up federative login on the development environment

The default application settings (parameters.yml.dist) are tailored to
the development VM - everything including federative login should work
out-of-the-box.

In order to actually login onto the dashboard you must first configure
the openconext components on the VM. Below steps will guide you trough
the process.

## Configure the dashboard as SP in service registry

 - Visit https://serviceregistry.dev.support.surfconext.nl/
 - Enter username 'admin' on the mujina IDP login form (empty password)
 - Click 'Create connection'
 - Enter connection ID: 'https://spdashboard.dev.support.surfconext.nl/saml/metadata'
 - Fill out 'Create entity fromURL': https://spdashboard.dev.support.surfconext.nl/saml/metadata'
 - Set the type to 'SAML 2.0 SP'
 - Click 'Create'
 - Then set the state to 'Production'
 
You should now be able to successfully login!
