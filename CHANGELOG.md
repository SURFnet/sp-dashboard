## 1.1.0
New features:
 - NameId format can now be configured in the entity forms
 - Client side validation was added to the entity forms
 - XML Metadata import errors are now shown on the form as validation errors (on top of the page) 
 - The CRM ID field is no longer required (please run migration `20180502134816`)
 - Added support for multiple admin teams. Can be configured using the `administrator_teams` parameter. The previously used
   `administrator_team` parameter has been discontinued and is no longer supported. 

Improvements:
 - Symfony was upgraded to version 3.4
 - Logo validation messages have been made more explicit
 - Lexik translation bundle was upgraded (please update database schema to make it work)
 
## Older versions
 - Release notes for older version can be found on the github [releases](https://github.com/SURFnet/sp-dashboard/releases) page.