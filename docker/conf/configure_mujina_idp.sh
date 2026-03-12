#!/bin/bash
# Configures the Mujina IdP test user attributes for Cypress tests.
# Waits for Mujina to be available before sending the PUT request.

until wget -q --spider --no-check-certificate https://mujina-idp.dev.openconext.local/api/attributes 2>/dev/null; do
    printf '.'
    sleep 1
done

curl -k -s -X PUT \
  -H 'Content-Type: application/json' \
  -d '{
    "urn:mace:dir:attribute-def:uid":                           ["Tiffany"],
    "urn:mace:dir:attribute-def:cn":                            ["Tiffany Aching"],
    "urn:mace:dir:attribute-def:givenName":                     ["Tiffany"],
    "urn:mace:dir:attribute-def:sn":                            ["Aching"],
    "urn:mace:dir:attribute-def:displayName":                   ["Tiffany Aching"],
    "urn:mace:dir:attribute-def:mail":                          ["tiffany@example.com"],
    "urn:mace:terena.org:attribute-def:schacHomeOrganization":  ["dev.openconext.local"],
    "urn:mace:dir:attribute-def:eduPersonPrincipalName":        ["tiffany@dev.openconext.local"],
    "urn:oasis:names:tc:SAML:attribute:subject-id":             ["tiffany@dev.openconext.local"],
    "urn:mace:dir:attribute-def:isMemberOf": [
      "urn:collab:group:dev.openconext.local:demo:openconext:org:surf.nl",
      "urn:collab:group:dev.openconext.local:demo:openconext:org:ibuildings.nl",
      "urn:collab:group:dev.openconext.local:dev:openconext:local:spd_admin"
    ]
  }' \
  https://mujina-idp.dev.openconext.local/api/attributes

echo "Mujina IdP attributes configured"
