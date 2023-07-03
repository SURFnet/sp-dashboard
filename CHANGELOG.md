## 5.0.1
- Repair jira_enable_test_mode feature flag #592

## 5.0.0
Main change made in this new major release is the upgrade to Symfony 5. These 
pull requests make up the rest of the changes:

- Symfony 5 upgrade #565
- Upgrade Symfony webtests to utilize Panther #576
- Move status logic to Manage entity #577
- Add a default text for revision notes #583
- Use global constants iso a string #582

## 4.4.5
**Bugfix**
- Show secrets modal oidc after connection request #584

## 4.4.4
**Bugfix**
- Add default text for revision notes #583

## 4.4.3
**Bugfix**
- Repair positioning of plus button #578
- 
## 4.4.2
**Bugfix**
- Repair button change request #575

## 4.4.1
**Backport**
- Include 4.3.2 into the 4.4 tier of releases (More Label and translation adjustments #574)

## 4.4.0
**Feature**
- Added the IdP connection request feature for production entities #555 

## 4.3.2
**Feature**
More Label and translation adjustments #574

## 4.3.1
**Feature**
Label and translation adjustments in attributes.json #567

## 4.3.0
The 4.3.0 release seems somwhat meager compared to the vast ammount of changes performed in the 4.2.0 release.
Don't be fooled tho. As this release is introducing a major overhaul on the way entity attributes (grants)
are handled in the application. This error prone construction of having hard-coded references to the supported 
attributes/grants resulted in many errors and bugs throughout the lifetime of SPD.

Having these attributes in a configurable json file makes managing them much easier.

**Features**
- Dynamically handle SAML attributes (OIDC grants) from attributes.json configuration #537 #528 #531

**Bugfix**
Handle grants and redirect urls correctly when updating an OIDC entity #551

## 4.2.2
**Bugfix**
- Repair publish call during ACL save (this is already fixed in develop 4.4)

## 4.2.1
**Bugfix**
- Ignore ACS locations that do not have the HTTP-POST Binding. #561

## 4.2.0
**Feature**
- Support more than one ACS location #521 #547
- Implement production entity change request #522
- Add a change request overview for production entities #542
- Show flash message when privacy messages are changed #517
- Visual feedback after creating, editing and deleting a service or entity #520
- Allow closing of flash messages #532

**Bugfixex**
- Clear selected service on service switcher when serving sp dashboard #527
- Create a Jira ticket after creating an entity change request #523
- Disallow colons in a OIDC clientId #526
- Transform Jira reporter name to custom field #538
- Set only email on custom reporter Jira field #541
- Remove flash message from production edit action #545
- Refrain from adding epTID for OIDCNG #514

**Maintenance**
- Venture into using the MERGE WRITE Manage Endpoint for updating Entities #516
- Filter out resolved Jira tickets #548
- Repair Service overview h2 title issue #549

**Chores**
- Install JS and PHP 3rd party dependencies #515
- Set the Timezone on the PHP FPM container #530

## 4.1.0
**New features**
- Add the teamId to the service switcher #501
- Log authenticated user #495

**Bugfixes**
- Team select lists styling was broken #503
- Correct faulty protocol mapping on oauth2 resource server and add a test #505
- Jira ticket status was not updated correctly for 'request for deletion' entities #513
- Small bugfixes: #502 #504 #506

## 4.0.1
**Bugfixes**
- fix broken modals after oidc creation & client secret reset

**Development**
- use the prebuilt openconext containers

## 4.0.0
**Features**
- integrate team management in the SPD

**Bugfixes**
- show correct translations for oidc attributes

**Development**
- move CI to github actions
- add teams to the docker container

## 3.3.1
**Features**
- Lowercase protocol & hostname in redirect url

**Bugfixes**
- Only update acl fields on edit idp whitelist

## 3.3.0
**Features**
- add a global site notice so admins can alert users to changes / important news
- add support for the organizational unit name attribute
- ensure both test and production get their own oidc modal (rather than sharing one)
- add a redirect when hitting the log out link

**Bugfixes**
- improve error message when creating SAML entities with an id that already exists
- fix normal user redirect after deleting a published entity
- ensure name is mandatory
- hide edit for production entity on status removal requested
- show the correct descriptions per language

**Security**
- disable trace and track methods
- disable showing the Apache version in headers

## 3.2.1
**Bugfix**
 - Prevent double JQuery installations #467 

## 3.2.0
**Feature**
 - Add support for Client Credential Clients  #445

## 3.1.2
**Bugfixes**
- Add 4 missing attributes when viewing (not editing) an entity:
  - schacHomeOrganization
  - schacHomeOrganizationType
  - shacpersonalUniqueCode
  - eduPersonTargetedID (not shown for oidc entities)
- ensure ACL is not copied when using a test entity as a template for a production entity
- ensured motivations for attributes need to be given on test, as was already the case on production
- fix two small issues which kept popping up in the logs

**Infra**
- add cache headers for the production Apache docker image
- use the latest openconext docker image

**Security**
- bump version of SSRI dependency to resolve security issue

## 3.1.1
**Bugfixes**
- Resolved client secret reset issues #443
   - Send the Organization names to Manage when performing a secret reset
   - Allow client reset for 'requested production publication' production entities
   - Prevent loss of connected Resource Servers during secret reset

## 3.1.0

**Bugfixes**
 - Only trigger privacy warning when enabled
 - Show privacy links button for regular users
 - Ensure exclude from push coin value is honoured
 - Repair faulty status implementation
 - Ensure production entities can be published once a test entity is published
 - Fix Jira status handling
 - Ensure APR attributes is an object when empty

**Cleanup**
 - Removed some obsolete config parameters and fixed some typos

**Infra**
 - Add Opcache to FPM Docker image
 - Use the latest php-fpm and Apache images in the Docker dev container
 - Make Docker develop container compatible with Xdebug 3

## 3.0.0
An updated theme, further deprovisioning of the ORM layer and some nifty changes to the entity actions.

**Features**
 - Adjust create new entity modal #408
 - The entity details screen was replaced by a detailed service overview page #392
 - A Docker dev env was introduced, replacing Vagrant and Ansible with Docker #407
 - Use `oauth20_rs` Manage entity scheme for resource servers #409
 - The OIDCng restriction is no longer configurable. OIDCng for all! #396
 - Organization name is now manageble for Services #397  
 - Drop attribute constraint, an entity without ARP is allowed #399
 - Allow setting multiple grants #412
    
**Bugfixes**
 - Remove trailing slashes from ClientID #402
 - Refrain from overwriting Manage tracked attributes #421

**Improvements**
Infra: 
 - Apache: Set the Referer policy header #393
 - Register required php extensions in composer.json #368
     
Jira:
 - Stop matching the Jira status to the entity status #394
 - Consider closed and resolved status from Jira #419

Other:
 - Remove default scope from Oidcng RP entities #404
 - Remove remaining OIDC crud #405
 - Remove all leftover Entity entity references #406
 - Support attributes with multiple values #413
 - Allow Resource Servers from outside of RP's team #418
 - Add info fields to create entity titles #415

## 2.7.2

**Bugfix**
Set correct publication state for prod entities PART II #394

## 2.7.1

**Bugfix**
Set correct publication state for prod entities #390

## 2.7.0

**Feature**
Removing the Draft entity state was the main goal of this release. The following work was done to reach that point

* Move the ManageEntity to the domain layer #375 
* Remove save button from entity forms #381
* Deprovision the oidcng playground uris #383 
* Ensure client secret set on copy to production #384
* Inject Jira ticket types in EntityService #387

**Improvements**
* Build Docker images on Github Actions #377
* Docker php fpm: Clear cache after installation #386
* Docker: Add content security policy to the docker httpd config #385
* Github actions: Move to the new GitHub container registry, which allo… #388
* Docker: Migrate to one multistage Docker file #389
 
**Security updates**
* Bump elliptic from 6.5.2 to 6.5.3 #380
* Bump http-proxy from 1.18.0 to 1.18.1 #382

## 2.6.5
When testing 2.6.4 another bug was encountered. This bug pushed the memberOf attribute to manage which was empoty and resulted
therefore in the removal of the attribute in Manage which shouldn't occur.

**Bugfix**
* Remove memberOf as managed attribute #379 

## 2.6.4
There was a bug introduced in 2.5 which did always overwrite the attributes from manage. This prevented the removal of
attributes when one was removed.

**Bugfix**
* Merge manage attributes only #378

## 2.6.3
In a previous release the Parsley form validation became broken. Resulting in allowing certain redirect URL restrictions to pass validation.

**Bugfix**
* Enable and fix Url validation #373

## 2.6.2
**Bugfix**
* Prevent use of inline-style as this causes a CSP warning #374

## 2.6.1
**Features**
* Finish Service changes started in #362 #372
* Increase CSP strictness #369

**Bugfix**
* Add entity.edit.information.redirectUrls to translations #370 

## 2.6.0
**Features**
* Make OIDC entities read-only #354
* Add institution fields to Service #362 
* Display more detailed resource servers in client detail view #365
* Allow reverse redirect URLs #366
 
**Visual changes**
* Make form labels normal casing #357
* Match SPD header styling match that of IdPD #358 

**Bugfix**
* Enable content security policy #339 
* Set missing information balloons #347
* Only preserve the exclude-from-push flag on client secret reset #342
* Parse OIDCng clientID correctly on secret modal #351
* Only push when not excluded from push for client reset #361

**Other changes and chores**
* Prevent Jira ticket on prod client secret reset #364 
* Postpone Jira publication request ticket creation #363 
* Remove assignee and reporter from Jira config #345
* Adjust translations #344
* Allow loopback addresses for redirect uris #337 
* Make secret generator url encode safe #346
* Improve URL validation disallow use of IP address #350
* Stop showing EPTI on detail views #353
* Validate the privacyPolicyUrl field #352
* Add composer to Ansible for installation #359
* Security updates #360

## 2.5.4
**Feature**
* Do not overwrite the source & value from an attribute if set in Manage #356
* Push metadata to production after secret reset #355

## 2.5.3
**Bugfix**
* Remove excessive OIDCng RS ARP attribute translations #349

## 2.5.2
**Bugfix**
* Interpret missing exclude from push correctly #348

## 2.5.1
**Bugfix**
* Only preserve the exclude-from-push flag on client secret reset #342
* Reuse scope attribute, preventing overwriting them #341 
* Prevent overwriting of ARP motivations #340

## 2.5.0
**Bugfix**

* Check ClientID existence without protocol #336
* Prevent overriding the 'exclude_from_push' attribute #335 
* Prevent overwriting of attributes when (re) publishing entities #334
* Add missing entity.edit.information translations #333

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
