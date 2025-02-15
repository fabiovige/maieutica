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
    .version()
    .sourceMaps()
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
    });

if (mix.inProduction()) {
    mix.version();
}
