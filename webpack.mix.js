const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css')
    .copy('resources/images/logo_login.png', 'public/images')
    .copy('resources/vendor', 'public/vendor')
    .version()
    .sourceMaps();

if (mix.inProduction()) {
    mix.version();
}
