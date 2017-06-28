@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Não foi possível continuar.</h3>
        </div>
    </div>
    <div class="form-bottom">
        <p class="text-center">Sua instituição não autorizou seu perfil.</p>
        <p class="text-center">{{ $error }}</p>
    </div>
@endsection
