@extends('templates.admin')

@section('header')
    <section class="content-header">
        <h1>
            Usuário: {{ $entry->name }}
            <small>ID: {{ $entry->id }}</small>
        </h1>
        <ol class="breadcrumb">
            <li>
                <a href="{{ backpack_url('admin') }}">{{ config('backpack.base.project_name') }}</a>
            </li>
            <li><a href="{{ backpack_url('users') }}">Usuários</a></li>

        </ol>
    </section>
@endsection


@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">
                Detalhes da instituição
            </h3>
        </div>

        <div class="box-body">
            <div class="model-details user-details">
                <div class="user-image">
                    <img src="{{ $entry->profile_pic_url or asset('images/user-placeholder.png') }}">
                </div>

                <ul class="properties">
                    <li class="property"><span class="column-name">Nome</span> {{ $entry->name }}</li>
                    <li class="property"><span class="column-name">Usuário desde</span> {{ $entry->created_at }}</li>
                    <li class="property">
                        <span class="column-name">E-mail</span>
                        @if ($entry->email)
                            <a href="mailto:{{$entry->email}}">{{$entry->email}}</a>
                        @endif
                    </li>
                    <li class="property"><span class="column-name">Telefone</span> {{ $entry->phone_number }}</li>
                    <li class="property"><span class="column-name">Perfil</span> {{ $entry->profile }}</li>
                    <li class="property"><span class="column-name">Curso</span> {{ $entry->course }}</li>
                    <li class="property"><span class="column-name">Bairro</span> {{ $entry->location }}</li>
                    <li class="property"><span
                                class="column-name">Motorista</span> {{ $entry->car_owner ? 'Sim' : 'Não' }}</li>
                    @if ($entry->car_owner)
                        <li class="property"><span class="column-name">Modelo do carro</span> {{ $entry->car_model }}
                        </li>
                        <li class="property"><span class="column-name">Placa do carro</span> {{ $entry->car_plate }}
                        </li>
                        <li class="property"><span class="column-name">Cor do carro</span> {{ $entry->car_color }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endsection
