@extends('backpack::layout')

@section('header')
    <section class="content-header">
        <h1>
            Instituição: {{ $institution->name }} <small>ID: {{ $institution->id }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url(config('backpack.base.route_prefix', 'admin')) }}">{{ config('backpack.base.project_name') }}</a></li>
            <li><a href="./">Instituições</a></li>

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
            <div class="model-details institution-details">
                <ul class="properties">
                    <li class="property"><span class="column-name">ID</span> {{ $institution->id }}</li>
                    <li class="property"><span class="column-name">Nome</span> {{ $institution->name }}</li>
                    <li class="property"><span class="column-name">Labels</span> {{ $institution->going_label }} / {{ $institution->leaving_label }}</li>
                    <li class="property">
                        <span class="column-name">URL de autenticação</span>
                        <a href="{{ $institution->authentication_url }}">{{ $institution->authentication_url }}</a>
                    </li>
                    <li class="property"><span class="column-name">Senha</span> {{ $institution->password }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">
                Campi
            </h3>
        </div>

        <div class="box-body">
            <ul>
                @foreach($campi as $campus)
                    <li>
                        <span class="campus-color" style="background-color: {{ $campus->color }}"></span>
                        <a href="./campus/{{ $campus->id }}">{{ $campus->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
