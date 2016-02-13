@extends('templates.admin')

@section('conteudo')
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="title">
                Taxa de Carbono Economizada
            </span>

            @include('includes.period-search-form', [
                'formMethod' => 'get',
                'formAction' => action("CarbonTaxController@carbonTax"),
                'defaultStart' => old('start', $start->format('d/m/Y')),
                'defaultEnd' => old('end', $end->format('d/m/Y'))
            ])

        </div>

        @include('includes.errors')

        <h1 class="text-center {{ $taxa ? 'carbon-tax-saved' : 'carbon-tax-nothing-saved'  }}">
            @if($taxa)
                <span>{{ $taxa }}</span>
            @else
                <span>Não houve economia nesse período...</span>
            @endif
        </h1>
    </div>

@endsection
