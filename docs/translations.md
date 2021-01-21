# Translations

The dashboard only supports the english language. Translations are
used to allow administrators to modify messages. The [Lexik translation bundle](https://github.com/lexik/LexikTranslationBundle/)
is used to provide the translation interface (/translations).

Default translations can be found in the Resources/translations folder
in app/ and each bundle.

To import the default translations into the lexik database, run the
`import-translations` ant target. This should be done after each
deployment.

Updating translations can be done with the command `php72 bin/console lexik:translations:import -c -f`.  
**Note:** if you see notifications like these: `Importing "/vagrant/vendor/lexik/translation-bundle/Resources/translations/LexikTranslationBundle.en.yml" ... 0 translations` then you need to run `./bin/console assets:install` in your vagrant folder **inside** the box.


Translation tokens ending with `.html` will be loaded in a WYSIWYG editor in the translation interface. These
translations are stored without input validation or sanitization. Therefore we need to strip the allowed tags in the
template. To do so in twig use the wysiwyg filter. For example: `{{ 'service.edit.comments.html'|trans|wysiwyg }}` 
