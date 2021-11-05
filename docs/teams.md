# Testing teams integration

## 1. API documentation

Can be found at [this github page](https://github.com/OpenConext/OpenConext-Teams-NG/wiki)

## 2. Ensure teams works

1. open Manage and search for `teams.vm.openconext.org` in the tab `relying parties`
2. ensure that `voot` has been coupled as a resource server & SAVE + PUSH if it wasn't yet
3. go to scopes & add `groups`
4. go to the resource server `voot` & add `groups` in the tab `metadata`

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
