var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .addEntry('app', './app/js/application.js')
    .addStyleEntry('global', './app/scss/application.scss')
    .addLoader({ test: /\.scss$/, loader: 'import-glob-loader' })
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableTypeScriptLoader()
    .enableSassLoader()
    .disableSingleRuntimeChunk()
    .configureBabel(() => {}, {
        useBuiltIns: 'entry',
        corejs: 3
    })
;

module.exports = Encore.getWebpackConfig();
