# Attribute release policy

Attributes requested by the SP can be included when adding a SAML 2.0 SP or an OIDC 1.0 Relying Party. A list of 
supported attributes are listed on the add/edit forms. The Service can then choose and motivate which of these 
attributes should be released to them by the IdP (EngineBlock).

The list of attributes can be managed via configuration in SP Dashboard. These attributes would be hard code previously.
As of fall 2022, these attributes have been made configurable via the `app/config/attributes.json`.

## Structure of attributes.json
An attribute is used in different contexts in SP Dashboard. For example, the text above states they are displayed on the
OIDC and SAML add/edit forms. But that is not the only place we reference those attributes. They are also displayed on 
the entity detail page. And are handled programatically on several different places. 

In order to have a good handle on the attributes, we need some basic context information about each attribute.

The JSON snipped below shows the data we track for each attribute.

```json
    {
      "id": "eduPersonTargetedID",
      "form" : {
        "excludeOnEntityType": [
          "oidcng"
        ],
        "translations" : {
          "en": {
            "label": "Edu person targeted ID attribute",
            "info": "The <strong>EDU person targetted</strong> id is used to identify an identity on our platform, bla bla bla bla"
          }
        }
      },
      "detail" : {
        "en": {
          "label": "Edu person targeted ID attribute",
          "info": "The <strong>EDU person targetted</strong> id is used to identify an identity on our platform, bla bla bla bla"
        }
      },
      "urns": [
        "urn:mace:dir:attribute-def:eduPersonTargetedID",
        "urn:oid:1.3.6.1.4.1.5923.1.1.1.10"
      ]
    }
```

### `id`
The Name the attribute is identified by in the application. This ID is somewhat arbitrary. But to keep good track of 
this config file. We advise you set the ID with a text that is based on the urn:mace attribute identifier. 

### `form`
Application form related settings must be configured under the `form` object.

#### `excludeOnEntityType` (optional)
An array of string, where you can set entity types that are NOT to display the attribute on their entity edit/detail screens.
The available options are: 'saml20' (sp), 'oidcng' (rp), 'oauth20_rs', 'oauth20_ccc'

#### `translations`
An object of translatable form related ui 'components'. The array of translations are keyed on the language it represents.
The language is to be configured in i18n country codes. At this point we only support English: `en`.

The object in turn is built up out of two properties

`label`: The form field text that describes the attribute field
`info` (optional): The inforamation popup text that is displayed next to the attribute form field. HTML formatting is allowed in this field.

### `detail`
The detail screen of an entity also shows the selected attributes. Of these attributes some aspects have been made translatable.
As with the `form` object. The `detail` array is also keyed on a i18n country code. Again, only `en` is supported at this point.

`label`: The entity detail field text that describes the attribute field
`info` (optional): The inforamation popup text that is displayed next to the attribute form field. HTML formatting is allowed in this field.

### `urns`
The urns identifying the attribute. These urns are used to identify the attributes while importing them from saml metadata.
But also used to send them of to manage. As in manage the attributes are identified by their human readable urn.

An array of urns is supported here. We implemented SPD to use the first entry as the urn we send to Manage. And use that field
to identify attributes that are read from manage. The other entries in this array are only used as aliasses while reading/importing 
SAML20 SP metadata into the entity create form.

## Quality assurance
The format of the `attributes.json` file is described in a JSON schema. On every automated quality assurance test run,
we verify the contents of the attributes against the schema. This should prevent issues with typo's or other errors 
creeping into the application.
