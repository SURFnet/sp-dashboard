# Legacy code
The aim of the project is to reuse as many code as possible from the 
[Service provider registration](https://github.com/SURFnet/Service-provider-registration) project. Some of this code is
harder to integrate the layered architecture we aim to deliver. When reused code cannot be easily integrated, a 
conscious decision is made to move that code to the Legacy namespace.

This does not change the testing strategy goals, described in the Test plan.

For each of the pieces of legacy code a small summary as to why the code is considered to be legacy code, should be 
added to this document.

## Metadata Parser & Fetcher
The Metadata parser and fetcher have been reused from the previous project. This feature had been implemented and tested
quite well in the SP registration project. Little refactoring was needed to run the code in the current project. 

Refactoring the code to suit our layered model did not fit the scope of the stories describing the functionality.  