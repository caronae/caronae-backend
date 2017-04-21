@extends('templates.error')

@section('title')
    Erro - 500
@endsection

@section('content')
    <h1>
        Um erro inesperado aconteceu.
    </h1>
    <p>
        O endereço que você tentou acessar gerou um erro inesperado no sistema. Por favor, informe os responsáveis do sistema sobre o erro.
    </p>
    <p>
        <a href="{{ url('/') }}">
            Para voltar à página principal, clique aqui.
        </a>
    </p>
@endsection