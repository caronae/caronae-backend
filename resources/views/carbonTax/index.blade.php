@extends('templates.admin')

@section('conteudo')
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="title">
                Taxa de Carbono Economizada
            </span>

            <form class="form-inline pull-right search-period-form" method="get" action="{{ action("RankingController@carbonTax") }}">
                <span>De:</span>
                <input
                        type="text"
                        class="form-control period-start"
                        name="start"
                        @if(!empty($_GET['start']))
                            value="{{ $_GET['start'] }}"
                        @else
                            value="{{ \Carbon\Carbon::now()->subMonth()->format('d/m/Y') }}"
                        @endif
                        placeholder="esse dia"
                        data-provide="datepicker"
                >
                <span>Até:</span>
                <input
                        type="text"
                        class="form-control period-end"
                        name="end"
                        @if(!empty($_GET['end']))
                            value="{{ $_GET['end'] }}"
                        @else
                            value="{{ \Carbon\Carbon::now()->format('d/m/Y') }}"
                        @endif

                        placeholder="esse dia"
                        data-provide="datepicker"
                >

                <button class="btn btn-primary">
                    <span class="glyphicon glyphicon-search"></span>
                    Mostrar
                </button>

            </form>

        </div>

        @if(!empty($errou))
            <div class="error-alert alert alert-danger alert-dismissible" role="alert">
                 {{ $errou }}
            </div>
        @endif

        @if($taxa)
        <h1 class="text-center">
            <span style="font-size: 7em;">{{ $taxa }}</span>
            <br>
            <span style="font-size: 1em;">g/Km</span>
        </h1>
        @else
            <h1 class="text-center">
                <span style="font-size: 1em;">Não houve economia nesse período...</span>
                <br>
                <br>
                <br>
            </h1>
        @endif

    </div>

@endsection

@section('js')

@endsection
