const mix = require('laravel-mix');
const path = require('path');

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css')
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
    mix.minify('public/js/app.js');
    mix.minify('public/css/app.css');
}
