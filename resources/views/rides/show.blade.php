@extends('backpack::layout')

@section('header')
    <section class="content-header">
      <h1>
        {{ $ride->title }} <small>ID: {{ $ride->id }}</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url(config('backpack.base.route_prefix', 'admin')) }}">{{ config('backpack.base.project_name') }}</a></li>
        <li><a href="{{ route('rides') }}">Caronas</a></li>
        <li class="active">{{ $ride->id }}</li>
      </ol>
    </section>
@endsection


@section('content')
    <a href="{{ route('rides') }}"><i class="fa fa-angle-double-left"></i> Voltar para todas as caronas</a><br><br>

    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Detalhes da carona
          </h3>
        </div>
        <div class="box-body">
            <div class="ride-info">
                <p><span class="column-name">{{ $ride->going ? 'Chegando' : 'Saindo' }} às</span> {{ $ride->date->format('H:i | l | d/m/Y') }}</p>

                <p><span class="column-name">Origem</span> {{ $ride->going ? $ride->neighborhood : $ride->hub }}</p>
                <p><span class="column-name">Destino</span> {{ $ride->going ? $ride->hub : $ride->neighborhood }}</p>
                <p><span class="column-name">Zona</span> {{ $ride->zone }}</p>
                <p><span class="column-name">Vagas</span> {{ $ride->slots }}</p>
                <p><span class="column-name">Concluída</span> {{ $ride->done ? 'Sim' : 'Não' }}</p>
                <p>
                    <span class="column-name">Rotina</span>
                    @if(!empty($ride->routine_id))
                        Sim (<a href="{{ route('ride', ['ride' => $ride->routine_id]) }}">{{ $ride->routine_id }}</a>)
                    @else
                        Não   
                    @endif
                </p>
            </div>


            <h4>Ponto de referência</h4>
            <p>{{ $ride->place }}</p>

            <h4>Rota do motorista</h4>
            <p>{!! implode('<br>', explode(', ', $ride->route)) !!}</p>

            <h4>Recado do motorista</h4>
            <p>{{ $ride->description }}</p>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Motorista
          </h3>
        </div>
        <div class="box-body">
            @include('vendor.backpack.crud.inc.user', ['u' => $ride->driver()])
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Caronistas
          </h3>
        </div>
        <div class="box-body">
            @each('vendor.backpack.crud.inc.user', $ride->riders(), 'u')
        </div>
    </div>
@endsection

@section('after_styles')
    <style type="text/css" media="screen">
        .ride-info {
            font-size: 16px;
            margin-bottom: 20px;
        }    
    </style>
@endsection

