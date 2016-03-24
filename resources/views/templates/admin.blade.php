<!doctype html>
<html>
<head data-url="{{ url() }}" data-token="{{ csrf_token() }}">

    <title>Area Administrativa | Caronaê</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/png" href="{{ asset('favicon-16x16.png') }}" sizes="16x16">

    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/DataTables/datatables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/templates/admin.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css')}}">
</head>
<body>

<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{action('AdminController@getIndex')}}">
                <img alt="Brand" src="{{ asset('images/logo_caronae.png') }}">
            </a>
            <p class="navbar-text">Caronaê</p>
        </div>
        <ul class="nav navbar-nav">
            <li>
                <a href="{{ action('UserController@index') }}">
                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
                    Usuários
                </a>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-stats" aria-hidden="true"></span>
                    Rankings de Usuarios
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="{{ action('RankingController@betterFeedback') }}">
                            Motoristas melhor avaliados
                        </a>
                    </li>
                    <li>
                        <a href="{{ action('RankingController@greaterRiders') }}">
                            Caronistas que pegam mais caronas
                        </a>
                    </li>
                    <li>
                        <a href="{{ action('RankingController@greaterDriversRiders') }}">
                            Motoristas que pegam mais caronas
                        </a>
                    </li>
                    <li>
                        <a href="{{ action('RankingController@greaterAverageOccupancy') }}">
                            Motoristas com maior ocupação média
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="{{ action('RideController@index') }}">
                    <span class="glyphicon glyphicon-road" aria-hidden="true"></span>
                    Caronas
                </a>
            </li>
            <li>
                <a href="{{ action('CarbonTaxController@carbonTax') }}">
                    <span class="glyphicon glyphicon-leaf" aria-hidden="true"></span>
                    Taxa de Carbono
                </a>
            </li>
        </ul>
        <a href="{{action('Auth\AuthController@getLogout')}}" class="navbar-text navbar-right">
            <span class="glyphicon glyphicon-log-out"></span>
            Logout
        </a>
        <a href="{{action('AdminController@getEdit')}}" class="navbar-text navbar-right">
            <span class="glyphicon glyphicon-pencil"></span>
            Editar
        </a>
        <p class="navbar-text navbar-right">
            Logado como {{ $user->name }}
        </p>

    </div>
</nav>
<!-- Fim da Navbar -->

<!-- Corpo do site -->
<div class="container">
    @yield('conteudo')
</div>
<!-- Corpo do site -->

<!--  Rodapé -->
<div class="footer">
    <div class="copyright">
        <p>
            © 2015 Caronaê. Todos os direitos reservados.
        </p>
    </div>
</div>
<!--  Rodapé -->

<script type="text/javascript" src="{{ asset('vendor/jquery-2.1.4.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('vendor/bootstrap/js/bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{ asset("vendor/DataTables/datatables.min.js") }}"></script>
<script type="text/javascript" src="{{ asset("vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js") }}"></script>
<script type="text/javascript" src="{{ asset("js/routes.js") }}"></script>
<script type="text/javascript" src="{{ asset("js/main.js") }}"></script>
@yield('js')
</body>
</html>
