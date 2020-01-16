## 2.4.0

**Feature**
* Make SP Dashboard PHP 7.2 compatible

**Bugfix**
* Upgrade Stepup-saml-bundle to version 4.1.8 #309
* Resolve certificate publication issue #324

**Security updates**
* Symfony to 3.4.36 
* Handlebars to 4.5.3
* Mixin-deep to 1.3.2

## 2.3.3

This is a security release that will harden the application against CVE 2019-3465
* Upgrade xmlseclibs to version 3.0.4 #318

## 2.3.2

**Feature**
* Add 'show oidc create options' feature toggle #314

**Bugfix**
* Ensure all OIDCng entities are shown in the entity listings #316

## 2.3.1

**Bugfix**
* Stop overwriting IdP ACL data when editing a published entity #315

## 2.3.0
This release adds OpenID Connect TNG Resource server support to SP Dashboard.

**Feature**
 * OIDC TNG Resource server support was added #298
 * Resource servers can be selected on RP (client) form #305 #306
 * Forms are no longer submitted on 'enter' #303

**Bugfix**
 * Contact type no longer mandatory #301
 * CommonName no longer mandatory #302
 
 Some other less noteworthy issues have been resolved in this release: #307 #308 #309 #312

## 2.2.1
This release consist of bugfixes after testing OIDC TNG support.

**Bugfix**
 * Push signature method to manage for OIDC #299
 * Correctly display ClientID values #295
 * Set a hard default value on access token validity #296
 * Set the OIDC TNG specific playground urls #297
 * Do not allow duplicate redirect URIs #291
 * Request EPTI by default for each OIDC TNG entity #290
 * Allow selection of Subject Type (NameIdFormat) #293
 * Refrain from sending a NULL secret to Manage #294
 * Enable an additional grant type #292

**Other improvements**
 * Ansible: Add some configuration parameters #289
 * Bump jquery from 3.3.1 to 3.4.0 #288

## 2.2.0
This release adds OpenID Connect TNG support to SP Dashboard. More details can be found in:

* Add oidcng support #275

## 2.1.7
**Feature**
 * Added a Jira test stand-in feature flag #287

**Bugfix**
 * Disabled HTML Purifier cache #284
 * Prevent error on empty Jira issue mock file #285

**Other improvements**
 * Ansible improvements #286 #279
 * Several security related updates #282 #283 #288
 
## 2.1.6
**Bugfix**
  * Escape the wysiwyg translations #281
  * Fix the statuscode for switch to nonexistent service #280
  * Add additional secyurity headers to response #278
  * Fix the entity delete acl #276
  * Fix the translations acl #277
 
## 2.1.5
**Bugfix**
 * Ensure the issues are indexed on Manage id #273

## 2.1.4

**Bugfix**
 * Do not save a local duplicate upon client reset #267
 * Consider IdP workflow state when retrieving IdP's for ACL action #270
 * Import correct invalid argument and throw it #272

**Security updates**
 * Bump js-yaml from 3.12.0 to 3.13.1 #269
 * Bump handlebars from 4.0.12 to 4.1.2 #268

**Other**
 * Ansible update #271

## 2.1.3
**Bugfix**
 * Bump fstream from 1.0.11 to 1.0.12 #266
 * Add default value for `representative_approved` column. #265

## 2.1.2
**Bugfix**
 * Fix idp validation from manage result #264

## 2.1.1
 * Add UI for entity ACL page #263
 * Add transactions middleware to commandbus #250
 * Fetch connection status for production entities from manage #257
 * Fix entity validators #260
 * Change the donutstate with empty service #252
 * Add issue repository for testing #261

## 2.1.0
 * Add translatable contact email placeholder #259
 * Rename 'team name' to 'team identifier' #258
 * Add translatable footer links #253
 * Show motivationless attributes on entity detail #255
 * Show OIDC items on entity details #256
 * Fix urn validator regex #251
 * Fix the playground url after copy to production #247
 * Prevent a space in the secret when copying #248
 * Update composer dependencies #249
 * Ansible cleanup #246

## 2.0.9
**Bugfix**
 * Add missing translations for the OIDC screens part 2 #244

**Chore**
 * Update twig to fix CVE-NONE-0001 #245

## 2.0.8
**Bugfix**
 * Add missing translations for the OIDC screens #243

## 2.0.7
**Bugfix**
 * Fix Manage query workflow state #242

## 2.0.6

**Bugfixes**
 * Change route on overview page to prevent 403 #241
 * Update the production connection status on publication #240

## 2.0.5

**Feature**
 * Change service switcher behavior #236
 * Drop 'surfconext-informed' support as a service status #235
 * Make the workflow state configurable #238
 
**Bugfix**
 - Fix donut status issue by replacing environment with status in DTO #237

**Chore**
 * Temporarily disable yarn audits build breaking capability #239

## 2.0.4

**Bugfix**
 - Correctly publish OIDC redirect URIs to Manage #233

**Improvement**
 - Provide a custom service overview for admin #234

## 2.0.3

**Bugfix**
 - Prevent new entities for checking Jira ticket #232

## 2.0.2

The main focus of this release was to fix some minor bugs to make a production ready release.

**Bugfixes:**
 - Fix manage certificate metadata #227
 - Ensure Jira tickets are created once #228
 - Fix oidc client id on detail page #229
 - Fix oidc client id on confirmation popup #230 
 - Makerelease: Make sure the node_modules dir is removed #231
 
## 2.0.1

The main focus of this release was to fix some minor bugs to make a production ready release.

**Bugfixes:**
 - Fix the dump method in twig template for prod #226
 - Fix setting entityId from clientId for OIDC entities from manage response. #220

## 2.0.0

This release sees the actual addition of OpenID Connect support (OIDC) in SP Dashboard. In release 1.5.x  we already 
hinted to the OIDC addition but this release finally adds support for said protocol. In addition to adding OIDC support,  
some security related chores have been undertaken. Most notable is the addition of Yarn Audit to the QA (build) tooling. 

**New feature**
 - Add OIDC support #189 #204 #206 
 - Create Jira issue after publishing to manage prod #221 #225
 - Split the entity overview page on environment #207

**Bugfixes:**
 - OpenID Connect related bugfixes: #205 #209 #216 #217 #219 #220 #203 #223

**Chores**
 - Upgrade npm dependencies #218
 - Improve security tests #224

## 1.5.2

The main focus of this release was to fix some minor bugs to make a production ready release.

**Bugfixes:**
 - Fix route after session timeout
 - Temporarily disable the OIDC entity type in modal

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
