var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .addEntry('app', './assets/js/application.js')
    .addStyleEntry('global', './assets/scss/application.scss')
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
