@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Ops! Não foi possível fazer seu login :(</h3>
        </div>
    </div>
    <div class="form-bottom error">
        <div class="title">Sua instituição não autorizou seu acesso ao Caronaê.</div>
        <div class="message">{{ $error }}</div>
        <div class="help-message">Acha que isto é um erro? Avise a gente pelo <a href="https://caronae.org/#contato">Falaê</a>!</div>
    </div>
@endsection
