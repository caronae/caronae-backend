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
                Detalhes do usuário
            </h3>
        </div>

        <div class="box-body">
            @include('users.user')
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">
                Caronas do usuário
            </h3>
        </div>

        <div class="box-body">
            @include('users.rides', ['entry' => $entry->rides()->get()])
        </div>
    </div>
@endsection
