# Use a Jira test stand in

By default SP dashboard is configured to not use the test stand in option in dev or production modes. To enable this 
feature. Simply configure the `jira_enable_test_mode: true` and `jira_test_mode_storage_path: '../var/issues.json'` to
your liking. The values above are sensible defaults.

The Jira interaction is now skipped, and all actions are handled in a happy flow manner. Resulting in a JSON file that
is filled with ticket id's that are then used by the application to track the Jira workflow state.

# Install local jira with Docker

This how to is based on the ivantichy Jira Docker image [1]

## Installation
In a to be determined folder:
 - For a setup with persistent storage on your host machine: 
    - `docker run --cidfile ~/jiracid --rm -p 8080:8888 -v /var/docker-data/postgres:/var/lib/postgresql/9.6/main -v /var/docker-data/jira-app:/var/atlassian/jira-app -v /var/docker-data/jira-home:/var/atlassian/jira-home ivantichy/jira:7.13.0 "$@" &`
 - For a one off run: (your data will be lost after the guest is taken down)
    - `$ docker run -d --name jira -p 8080:8888 ivantichy/jira:7.13.0`

Jira will now be available on `http://localhost:8888`

## Configuration
Visit Jira for the first time and the installation wizard will be triggered:

**On the 'Set up application properties' page:**

 - Set Application Title: 'SP Dashboard | development environment'
 - Set Mode: Private
 - Set Base URL: http://172.17.0.2:8080

Next generate a licence key with your atlassian account. note that this will only work for 90 days..

**On the 'Specify your license key' page:**

 - The licence key will have been auto filled
 - Click next to continue configuration.

This will take a while..

**On the 'Set up administrator account' page**
Set up your admin user be sure to save your account settings somewhere

**On the 'Set up email notifications' page**

 - I chose to configure this later/never.
 - Click finish

The installation will take some time to finish. After a while you will be forwarded to: http://localhost:8888/secure/WelcomeToJIRA.jspa and you should have a running installation.

## Set up for SP Dashboard

**Set up a project**

- Create a new kanban project with:
- Name: SURFconext-beheer
- Key: CXT

**Add the custom issue type**

- Go to the settings page
- Open the Issues tab
- Open the Issue types pane
- Click the 'Add issue type' button
- Set name: spd-delete-production-entity
- Set description: ...
- Make it a standard issue type

Next we need to configure the project to use the freshly made issue type.

- Open the Projects tab
- Click on the 'SURFconext-beheer' entry
- Click on the 'Issue types' menu item on the 'Project setting menu on the left'
- Add the spd-delete-production-entity type to the CXT project.

**Set up custom issue field**

We need to be able to set the entity id of an entity that is refered to in the story.

1. Go to the settings page
1. Open the Issues tab
1. In the menu on the left, in the FIELDS section, click 'Custom fields'
1. Click on 'Add custom field' on the top right of the page
1. Choose the 'Text Field (single line)' option
1. Set the name to: EntityID
1. Set the description to: entityID of the Service
1. Click the Create button

Repeat steps 4 through 8 with the 'Manage Id' custom field

Next we need the field to show up on the add/edit screens

- Go to the settings page
- Open the Issues tab
- In the menu on the left, in the FIELDS section, click 'Field configurations'
- Click screens for the 'EntityID' field
- Check all the places where you want this field to show up (I selected them all)

**Create users**

Repeat the steps below for the following users:

|Username|Name|
|--------|----|
|conext-beheer|Conext Beheerder|
|sp-dashboard|The SP Dashboard api user|


- Go to the settings page
- Open the User management tab
- Click the add user button
- Fill the form field, making sure you save the username/password somewhere.


[1](https://hub.docker.com/r/ivantichy/jira/)