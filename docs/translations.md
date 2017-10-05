# Translations

The dashboard only supports the english language. Translations are
used to allow administrators to modify messages. The [Lexik translation bundle](https://github.com/lexik/LexikTranslationBundle/)
is used to provide the translation interface (/translations).

Default translations can be found in the Resources/translations folder
in app/ and each bundle.

To import the default translations into the lexik database, run the
`import-translations` ant target. This should be done after each
deployment.

Translation tokens ending with `.html` will be loaded in a WYSIWYG editor in the translation interface. These
translations are also to be outputted unescaped in the template. To do so in twig use the raw filter. For example:
`{{ 'service.edit.comments.html'|trans|raw }}` 