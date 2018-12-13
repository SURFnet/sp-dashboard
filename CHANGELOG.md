## 1.5.1

The main focus of this release was on the Jira integration for entity removal request issue tracking. In order to make 
this release work update the parameters.yml according to the example given in the dist file.

**New feature**
 - Read the Jira delete request status #200
 - Save the Manage entity id on the Jira issue #199
 - Show entities on service delete confirmation page #198 (thanks @femke)
 
**Improvements**
 - Make ManageEntityAccessGrantedVoter more robust #196
 - Update service status indicators #197

## 1.5.0

**New features:**
 - Improved service overview #192
 - Entity details can now be displayed #193 #188
 
**Improvements**
 - Add missing favicon #187
 - Security updates where installed #194
 - OIDC support was added to the dev vm #191
 - Manage responses are converted to DTO #190

## 1.4.1

**Bugfixes:**
 - Fixed the test_request_delete_a_published_production_entity_jira_not_available web test

## 1.4.0

This release is the product of two development sprints. The main focus is on improving the service overview screen for
SP's and also add additional features for moderating entities and services.

**New features:**
 - Manage production connection is added and entities can be published to it [#161679714](https://www.pivotaltracker.com/story/show/161679714) [#158971322](https://www.pivotaltracker.com/story/show/158971322) [#161949112](https://www.pivotaltracker.com/story/show/161949112)
 - Services now have additional status fields [#161341178](https://www.pivotaltracker.com/story/show/161341178)
 - A new service overview page was created based on the status fields [#161719597](https://www.pivotaltracker.com/story/show/161719597) [#162112946](https://www.pivotaltracker.com/story/show/162112946)
 - Published production delete requests are now translated in a Jira issue [#162124271](https://www.pivotaltracker.com/story/show/162124271)
 - Services and entities can be deleted [#154841957](https://www.pivotaltracker.com/story/show/154841957) [#154518577](https://www.pivotaltracker.com/story/show/154518577)
 - Cleanup feature of pre 2.0.0 published production entities [162051868](https://www.pivotaltracker.com/story/show/162051868)

**Improvements:**
 - Service forms have been re-styled implementing a two column setup [#162038026](https://www.pivotaltracker.com/story/show/162038026)
 - Confirmation screens can now be rendered in a modal/dialog [#161720049](https://www.pivotaltracker.com/story/show/161720049)  
 - TypeScript was installed and a small portion of the existing Javascript was migrated to TS.
 - Front end QA tooling was added to the build tools.
 - Webpack Encore was upgraded to the latest version

**Bugfixes:**
 - The migrations have been squashed into a single migration to ensure the database schema can be created safely. [#161877201](https://www.pivotaltracker.com/story/show/161877201)

## 1.3.0

Note that there now is a requirement on Manage version >= 2.0.18. 

**New feature:**
 - Contact details of the publisher are added to the 'publish to production' mail message.
 - Attribute motivations are pushed in the new format to Manage

## 1.2.0

This release includes various improvements to the UI, mostly better warning and error messages.

## 1.1.0
**New features:**

 - NameId format can now be configured in the entity forms
 - Client side validation was added to the entity forms
 - XML Metadata import errors are now shown on the form as validation errors (on top of the page) 
 - The CRM ID field is no longer required (please run migration `20180502134816`)
 - Added support for multiple admin teams. Can be configured using the `administrator_teams` parameter. The previously used
   `administrator_team` parameter has been discontinued and is no longer supported. 

**Improvements:**
 - Symfony was upgraded to version 3.4
 - Logo validation messages have been made more explicit
 - Lexik translation bundle was upgraded (please update database schema to make it work)
 
## Older versions
 - Release notes for older version can be found on the github [releases](https://github.com/SURFnet/sp-dashboard/releases) page.
