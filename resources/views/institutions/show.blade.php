<?php
$campi = $entry->campi;
?>
@extends('templates.admin')

@section('header')
    <section class="content-header">
        <h1>
            Instituição: {{ $entry->name }} <small>ID: {{ $entry->id }} / {{ $entry->slug }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ backpack_url('home') }}">{{ config('backpack.base.project_name') }}</a></li>
            <li><a href="{{ backpack_url('institutions') }}">Instituições</a></li>
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
                    <li class="property"><span class="column-name">Nome</span> {{ $entry->name }}</li>
                    <li class="property"><span class="column-name">Labels</span> {{ $entry->going_label }} / {{ $entry->leaving_label }}</li>
                    <li class="property">
                        <span class="column-name">URL de autenticação</span>
                        <a href="{{ $entry->authentication_url }}">{{ $entry->authentication_url }}</a>
                    </li>
                    <li class="property"><span class="column-name">Senha</span> {{ $entry->password }}</li>
                    <li class="property">
                        <span class="column-name">Mensagem de login</span>
                        {!! $entry->login_message !!}</li>
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
