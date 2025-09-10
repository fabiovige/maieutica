const mix = require('laravel-mix');
const path = require('path');
const sass = require('sass');

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css', {
        sassOptions: {
            quietDeps: true,
            outputStyle: 'compressed',
            includePaths: ['node_modules']
        },
        implementation: sass
    })
    .copy('resources/images', 'public/images')
    .copy('resources/vendor', 'public/vendor')
    .copy('resources/js/pages', 'public/js/pages')
    .version()
    .sourceMaps()
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        plugins: [
            new (require('webpack')).DefinePlugin({
                __VUE_OPTIONS_API__: JSON.stringify(true),
                __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
                __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false)
            })
        ]
    });

if (mix.inProduction()) {
    mix.version();
}
