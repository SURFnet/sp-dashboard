# Testing teams integration

## 1. API documentation

Can be found at [this github page](https://github.com/OpenConext/OpenConext-Teams-NG/wiki)

## 2. Ensure teams works

In order to use this, you need to recreate some parts of your dev env:

- Delete the mongo database:

`docker volume rm sp-dashboard_spdashboard_mongo`

- Pull the new image:

`docker pull ghcr.io/openconext/openconext-deploy/openconext-core:feature_build_image_with_teams_included`

- Rebuild the OpenConext container

`docker-compose build openconext`

- Start it, wait some time until everything is started. Then you need to fix a scope provisioning issue in OpenConext deploy:

    - navigate to `https://manage.vm.openconext.org`
    - Open the scopes tab, and add the scope "groups" (name and description don't matter)
        - Move to the resource servers tab, and edit the resource server "voot.vm.openconext.org"
    - Remove the "groups" scope. And save it
    - Reopen it, and add it again (yes this is really how you get it up & running again)
    - Click on PUSH
    - You should now be able to open `https://teams.vm.openconext.org` (which in your /etc/hosts should point to 127.0.01)

## 3. Accessing the logs

These can be found on the openconext docker at `/var/log/teams/teams.log`.

From the cli of your host: `docker-compose exec openconext tail -50  /var/log/teams/teams.log`

## 4. Accepting an invite / testing with actual members

The docker container does not contain a functioning email service, and so sending an invite / creating a team does not actually send a mail.

Therefore: to test with an actual member, you need to do the following:
1. open the openconext docker cli
2. go to mysql (`mysql` on the cli)
3. select a uiid for one of the invited people: 
   1. `use teams;`
   2. `select invitation_uiid from invitations where id = ;` => fill in the id
4. accept the invite by going to the following url: `https://teams.vm.openconext.org/invitation/accept/$uiid_of_the_invite` where you replace the last bit with the uiid found in the previous step.

If you use the cli to connect to docker: `docker-compose exec openconext mysql -e 'use teams; select invitation_uiid from invitations where id = ;'` & so on.
