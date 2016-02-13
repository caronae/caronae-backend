<!doctype html>
<html>
<head data-url="{{ url() }}" data-token="{{ csrf_token() }}">

    <title>Area Administrativa | Caronaê</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="">

    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/DataTables/datatables.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker3.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/templates/admin.css')}}">
    <link rel="stylesheet" href="{{ asset('css/main.css')}}">
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
                    <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
                    Usuários
                </a>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-signal" aria-hidden="true"></span>
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
                            Caronistas com mais caronas
                        </a>
                    </li>
                    <li>
                        <a href="{{ action('RankingController@greaterDriversRiders') }}">
                            Motoristas com mais caronas
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
                    <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
                    Caronas
                </a>
            </li>
            <li>
                <a href="{{ action('CarbonTaxController@carbonTax') }}">
                    <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
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

<script src="{{ asset('vendor/jquery-2.1.4.min.js')}}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{ asset("vendor/DataTables/datatables.min.js") }}"></script>
<script type="text/javascript" src="{{ asset("vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js") }}"></script>
<script type="text/javascript" src="{{ asset("js/main.js") }}"></script>
@yield('js')
</body>
</html>
