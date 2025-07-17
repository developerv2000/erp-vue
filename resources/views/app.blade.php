<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/main/favicon.png') }}">

    @auth
        @routes
    @endauth

    @vite(['resources/css/app.css', 'resources/js/core/boot/app.js'])

    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
