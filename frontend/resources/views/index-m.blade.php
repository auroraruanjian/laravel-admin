<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>{{ env('APP_NAME') }}</title>

    {{-- Add to home screen for Android and modern mobile browsers --}}
    <meta name="theme-color" content="#000">

    {{-- Add to home screen for Safari on iOS --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="app">
    <!-- <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon-152x152.png"> -->
    {{-- Add to home screen for Windows --}}
    <!-- <meta name="msapplication-TileImage" content="/images/icons/msapplication-icon-144x144.png"> -->
    <meta name="msapplication-TileColor" content="#000000">

{{--    <link href="{{ mix( 'm/css/vendor.css' ) }}" type="text/css" rel="stylesheet" />--}}
</head>
<body>
    <div id="app"></div>
    <script src="{{ mix( '/js/manifest.js' ) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
    <script src="{{ mix( '/js/vendor.js' ) }}"></script>
    <script src="{{ mix( '/js/app-m.js' ) }}"></script>
</body>
</html>
