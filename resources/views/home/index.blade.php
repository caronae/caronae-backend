@extends('backpack::layout')

@section('header')
    <section class="content-header">
      <h1>
        Painel
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url(config('backpack.base.route_prefix', 'admin')) }}">{{ config('backpack.base.project_name') }}</a></li>
        <li class="active">{{ trans('backpack::base.dashboard') }}</li>
      </ol>
    </section>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="box box-default stats">
                <div class="box-header with-border">
                    <div class="box-title">Estatísticas</div>
                </div>

                <div class="box-body">
                  <img src="{{ asset('images/logo_caronae.png') }}" alt="Logo Caronaê" class="logo">

                  <h4>Caronas</h4>
                  <ul class="stats">
                    <li>
                      <span class="column-name">Criadas</span>
                      {{ number_format(Caronae\Models\Ride::count(), 0, '', '.') }}
                    </li>
                    <li>
                      <span class="column-name">Concluídas</span>
                      {{ number_format(Caronae\Models\Ride::finished()->count(), 0, '', '.') }}
                    </li>
                  </ul>

                  <br>

                  <h4>Usuários</h4>
                  <ul class="stats">
                    <li>
                      <span class="column-name">Total</span>
                      {{ number_format(Caronae\Models\User::count(), 0, '', '.') }}
                    </li>
                    <li>
                      <span class="column-name">Completaram o cadastro</span>
                      {{ number_format(Caronae\Models\User::where('email', '!=', '')->count(), 0, '', '.') }}
                    </li>
                    <li>
                      <span class="column-name">Motoristas</span>
                      {{ number_format(Caronae\Models\User::where('car_owner', true)->count(), 0, '', '.') }}
                    </li>
                  </ul>

                </div>
            </div>
        </div>
    </div>
@endsection
