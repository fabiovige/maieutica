# Projeto Maiêutica

## Visão Geral

- Sistema para avaliação cognitica de crianças até 6 anos. è utilizado para avaliação a tabela DENVER II muito utilizada para avaliar crianças com algum tipo de atraso cognitivo.
- O sistema possui gerenciamento de crianças, usuarios, permissçoes e papeis, checklists, gráficos, PDFs etc
- Possui Desenvolvimento da criança, Avaliações, Planos e muito mais

## features

- O sistema usa tecnologias híbridas como Laavel 9, Blade e Vue.js tudo junto
- O banco de dados é mysql com factores, seeders
- Possui envio de e-mails para atualizar senha
- Obsever quando um usuario é criado ou alterado e envia um e-mail

O sistema está em fase BETA mas vai precisar de melhorias, preciso que me ajude a refatorar aos poucos ee deixar o sistema mais estável, robusto possível.

## composer

{
    "name": "laravel/laravel",
    "type": "project",
    "description": "The Laravel Framework.",
    "keywords": ["framework", "laravel"],
    "license": "MIT",
    "require": {
        "php": "^8.0.2",
        "ext-json": "*",
        "arcanedev/log-viewer": "*",
        "barryvdh/laravel-dompdf": "*",
        "datatables.net/datatables.net": "dev-master",
        "datatables.net/datatables.net-dt": "dev-master",
        "elibyy/tcpdf-laravel": "^9.1",
        "fruitcake/laravel-cors": "^3.0",
        "guzzlehttp/guzzle": "^7.2",
        "laracasts/flash": "^3.2.1",
        "laravel/framework": "^9.0",
        "laravel/sanctum": "^2.14",
        "laravel/tinker": "^2.7",
        "laravel/ui": "^3.4",
        "laravellegends/pt-br-validator": "^9.1",
        "renatomarinho/laravel-page-speed": "^2.1",
        "spatie/laravel-permission": "^6.9",
        "yajra/laravel-datatables-oracle": "*"
    },
    "require-dev": {
        "barryvdh/laravel-ide-helper": "^2.15",
        "fakerphp/faker": "^1.9.1",
        "laravel/pint": "^1.17",
        "laravel/sail": "^1.0.1",
        "lucascudo/laravel-pt-br-localization": "^1.2",
        "mockery/mockery": "^1.4.4",
        "nunomaduro/collision": "^6.1",
        "phpunit/phpunit": "^9.5.10",
        "spatie/laravel-ignition": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "app/helpers.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force",
            "@php artisan ide-helper:generate",
            "@php artisan ide-helper:meta"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi"
        ],
        "clear": [
            "composer dumpautoload -o",
            "@php artisan clear-compiled",
            "@php artisan cache:clear",
            "@php artisan route:clear",
            "@php artisan view:clear",
            "@php artisan config:clear"
        ],
        "fresh": [
            "@php artisan migrate:fresh --seed"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": [
                "barryvdh/laravel-ide-helper"
              ]
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}


## package.json

{
    "private": true,
    "scripts": {
        "dev": "npm run development",
        "development": "mix",
        "watch": "mix watch",
        "watch-poll": "mix watch -- --watch-options-poll=1000",
        "hot": "mix watch --hot",
        "prod": "npm run production",
        "production": "mix --production"
    },
    "devDependencies": {
        "@popperjs/core": "^2.10.2",
        "autoprefixer": "10.4.5",
        "axios": "^0.21",
        "bootstrap": "^5.1.3",
        "bootstrap-icons": "^1.8.3",
        "jquery": "^3.6.0",
        "jquery-ui": "^1.13.1",
        "laravel-mix": "^6.0.6",
        "lodash": "^4.17.19",
        "postcss": "^8.1.14",
        "resolve-url-loader": "^5.0.0",
        "sass": "^1.79.4",
        "sass-loader": "^11.0.1",
        "sweetalert2": "^11.4.17",
        "vue-loader": "^16.8.3",
        "vue-sweetalert2": "5.0.5"
    },
    "dependencies": {
        "chart.js": "^3.9.1",
        "jquery-mask-plugin": "^1.14.16",
        "vee-validate": "^4.6.7",
        "vue": "^3.2.37",
        "vue-chart-3": "^3.1.8",
        "vue-jquery": "^1.0.6",
        "vue-jquery-mask": "^2.0.0",
        "vue3-loading-overlay": "^0.0.0",
        "vue3-select2-component": "^0.1.7"
    }
}



## Estrutura atual do projeto
