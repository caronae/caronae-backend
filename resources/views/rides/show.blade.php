@extends('templates.admin')

@section('header')
    <section class="content-header">
      <h1>
        {{ $entry->title }} <small>ID: {{ $entry->id }}</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ backpack_url('admin') }}">{{ config('backpack.base.project_name') }}</a></li>
        <li><a href="{{ backpack_url('rides') }}">Caronas</a></li>
        <li class="active">{{ $entry->id }}</li>
      </ol>
    </section>
@endsection


@section('content')
    <a href="{{ backpack_url('rides') }}"><i class="fa fa-angle-double-left"></i> Voltar para todas as caronas</a><br><br>

    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Detalhes da carona
          </h3>
        </div>
        <div class="box-body">
            <div class="ride-info">
                <p><span class="column-name">{{ $entry->going ? 'Chegando' : 'Saindo' }} às</span> {{ $entry->date->format('H:i | l | d/m/Y') }}</p>

                <p><span class="column-name">Origem</span> {{ $entry->going ? $entry->neighborhood : $entry->hub }}</p>
                <p><span class="column-name">Destino</span> {{ $entry->going ? $entry->hub : $entry->neighborhood }}</p>
                <p><span class="column-name">Zona</span> {{ $entry->zone }}</p>
                <p><span class="column-name">Vagas</span> {{ $entry->slots }}</p>
                <p><span class="column-name">Concluída</span> {{ $entry->done ? 'Sim' : 'Não' }}</p>
                <p>
                    <span class="column-name">Rotina</span>
                    @if(!empty($entry->routine_id))
                        Sim (<a href="{{ route('ride', ['ride' => $entry->routine_id]) }}">{{ $entry->routine_id }}</a>)
                    @else
                        Não   
                    @endif
                </p>
            </div>


            <h4>Ponto de referência</h4>
            <p>{{ $entry->place }}</p>

            <h4>Rota do motorista</h4>
            <p>{!! implode('<br>', explode(', ', $entry->route)) !!}</p>

            <h4>Recado do motorista</h4>
            <p>{{ $entry->description }}</p>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Motorista
          </h3>
        </div>
        <div class="box-body">
            @include('users.user', ['entry' => $entry->driver()])
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">
            Caronistas
          </h3>
        </div>
        <div class="box-body">
            @each('users.user', $entry->riders()->get(), 'entry')
        </div>
    </div>
@endsection

