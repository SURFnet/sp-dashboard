# Testing the front-end with different priviliges

When adjusting the designs for the site, you need to test changes both as a normal user and as a user with administrator access.

## Logging in as admin:
- make sure that a team identifier for a service which exists in your box is set in `parameters.yml` for the `administrator_teams` key.  By default, it should look like this: 
```
administrator_teams:
     - 'urn:collab:org:surf.nl'
     - 'urn:collab:org:dev.support.surfconext.nl'
```
Doing this ensures that when you log in you are a member of the teams listed there by default & that users of those teams have admin rights.

## Logging in as a "normal user":
- remove the content under the `administrator_teams` key in `parameters.yml`, so no key is present.
- ensure that you know the team identifiers for at least two existing services.
- when logging in to spdashboard with mujina, opt to add the `isMemberOf` attribute.  Pass in the team-identifiers you noted above.  Add the `isMemberOf` attribute once for each identifier.

## Setting up new teams / services:
If your box doesn't have enough services, or teams, you can set them up quite easily:
- go to your local [teams](https://teams.dev.support.surfconext.nl/)
- add a new team with you as a member.  You are an admin by default and that's exactly as you want it.
- create a new service in SP Dashboard (SPD) and when asked for the team identifier fill in that of the team you just made.  You can have only one service / team.  **Note:** you need to be logged in as an admin to set up new services.
- repeat steap 1-3 as necessary.
