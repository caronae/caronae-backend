@extends('templates.login')

@section('content')
    <div class="form-top">
        <div class="form-top-left">
            <h3>Login institucional</h3>
        </div>
    </div>
    <div class="form-bottom">
        <div class="message">
            {!! $login_message !!}
        </div>

        <div class="institution-login">
            <a class="btn btn-success" href="{{ $authentication_url }}">Continuar &raquo;</a>
        </div>
    </div>
@endsection