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
1. Find the invitation_uiid of the user you want to accept `docker-compose exec openconext mysql -e "select * from invitations \G" teams`
When accepting a new invite a new private window needs to be opened, otherwise the following error will show: "You are already a member of this Team."
2. Accept the invite by going to the following url: `https://teams.vm.openconext.org/invitation/accept/"invitation_uiid"` where you replace the text between the "" with the invitation_uiid found in the previous step.

Once this has been done you can make the team public so adding new members will be easier.
This can be done with the next steps:
1. Make sure that the user has the Owner role in `spdashboard.vm.openconext.org`.
2. In `https://teams.vm.openconext.org/my-teams` open the team and enable the PUBLIC TEAM and PUBLIC LINK boxes.
3. In a new private browser this link can be used to join with a new login session.

Subsequent team members can now join by using the public link within a new private browser.
