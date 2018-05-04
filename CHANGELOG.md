# Upcoming release
Notable changes
 - Client side validation was added to the form
 - The CRM ID field is no longer required (please run migration 20180502134816)
 - Added support for multiple admin teams. Can be configured using the `administrator_teams` parameter. The previously used
   `administrator_team` parameter has been discontinued and is no longer supported. 
 - Symfony was upgraded to version 3.4
 - Lexik translation bundle was upgraded (please update database schema to make it work)