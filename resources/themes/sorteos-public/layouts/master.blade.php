<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sorteos ITSON')</title>
    <link rel="shortcut icon" href="{{ \Settings::get('site_favicon') }}" type="image/png">

    <link rel="stylesheet" href="{{ asset('assets/themes/admin/plugins/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/themes/admin/plugins/bootstrap/dist/css/bootstrap.min.css') }}">
    {!! Theme::css('css/sorteos.css') !!}
    {!! \Html::style('assets/corals/plugins/toastr/toastr.min.css') !!}
    {!! \Assets::css() !!}
    @yield('css')
    @stack('partial_css')

    <script>window.base_url = '{!! url('/') !!}';</script>
    {!! \Html::script('assets/corals/js/corals_header.js') !!}
</head>
<body class="sorteos-page">

    @include('partials.header')

    <main class="srt-main">
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('assets/themes/admin/plugins/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    {!! \Html::script('assets/corals/js/corals_functions.js') !!}
    {!! \Html::script('assets/corals/js/corals_main.js') !!}
    {!! \Html::script('assets/corals/plugins/toastr/toastr.min.js') !!}
    {!! Assets::js() !!}

    @php \Actions::do_action('footer_js') @endphp

    @yield('js')

    @include('Corals::corals_main')
</body>
</html>
