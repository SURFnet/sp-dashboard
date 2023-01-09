var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableTypeScriptLoader()
    .addEntry('app', './assets/js/application.js')
    .addStyleEntry('global', './assets/scss/application.scss')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    // Convert sass files.
    .enableSassLoader(function (options) {
        options.sassOptions = {
            outputStyle: 'expanded',
            includePaths: ['public'],
        };
    })
    .addLoader({ test: /\.scss$/, loader: 'webpack-import-glob-loader' })
    .configureLoaderRule('eslint', loaderRule => {
        loaderRule.test = /\.(jsx?|vue)$/
    })
    .disableSingleRuntimeChunk()
    .configureBabel(() => {}, {
        useBuiltIns: 'entry',
        corejs: 3
    })
;

module.exports = Encore.getWebpackConfig();
