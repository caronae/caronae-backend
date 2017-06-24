<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Caronaê</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/png" href="{{ asset('favicon-16x16.png') }}" sizes="16x16">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/login.css') }}">

</head>

<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-2 header-line brown"></div>
        <div class="col-xs-2 header-line blue"></div>
        <div class="col-xs-2 header-line pink"></div>
        <div class="col-xs-2 header-line green"></div>
        <div class="col-xs-2 header-line orange"></div>
        <div class="col-xs-2 header-line red"></div>
    </div>
</div>

<div class="top-content">
    <div class="inner-bg">
        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-sm-offset-2 text">
                    <img src="{{ asset('images/logo_caronae_with_text.png') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3 form-box">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>

@yield('after_scripts')

</body>
</html>
