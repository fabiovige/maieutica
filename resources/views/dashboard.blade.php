<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title> {{ config('app.name') }} - {{ config('app.description') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ mix('/js/app.js') }}" defer></script>
</head>
<body>
    <div id="app">
        <!-- route outlet -->
        <!-- component matched by the route will render here -->
        <base-header></base-header>
        <router-view></router-view>
        <base-footer></base-footer>
    </div>
</body>
</html>
