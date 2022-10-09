<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> Maiêutica - clínica psicológica e terapias associadas</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body>

<div class="pagewrap">

    <main class="py-2">
        <div class="container">
            <div class="row mt-1">
                <div class="col-md-12 text-start">
                    TESTE
                </div>
            </div>

        </div>
    </main>
    <footer>
        <div class="container">
            <div class="row  py-4 d-flex justify-content-center">
                <p class="small text-center mb-0 mt-4">
                    &copy; {{ now()->format('Y') }} {{ config('app.name') }} {{ config('app.description') }}
                    <br>
                    v{{ config('app.version') }}
                </p>
            </div>
        </div>
    </footer>
</div>

<script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
