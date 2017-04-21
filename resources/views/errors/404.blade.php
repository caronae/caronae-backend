@extends('templates.error')

@section('title')
    Página não encontrada - 404
@endsection

@section('content')
    <h1>
        A página que você tentou acessar não foi encontrada.
    </h1>
    <p>
        O endereço que você tentou acessar não existe. Caso tenha escrito o endereço manualmente, verifique se o escreveu corretamente.
    </p>
    <p>
        <a href="{{ url('/') }}">
            Para voltar à página principal, clique aqui.
        </a>
    </p>
@endsection