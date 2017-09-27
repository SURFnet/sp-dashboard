var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .addEntry('app', './app/js/application.js')
    .addStyleEntry('global', './app/scss/application.scss')
    .enableSassLoader()
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
