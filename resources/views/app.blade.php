<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/main/favicon.png') }}">

    @routes

    @vite(['resources/css/app.css', 'resources/js/core/boot/app.js'])

    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
