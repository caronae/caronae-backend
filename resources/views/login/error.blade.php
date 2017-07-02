@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Não foi possível obter sua chave de acesso.</h3>
        </div>
    </div>
    <div class="form-bottom error">
        <div class="title">Sua instituição não autorizou seu acesso ao Caronaê.</div>
        <div class="message">{{ $error }}</div>
    </div>
@endsection
