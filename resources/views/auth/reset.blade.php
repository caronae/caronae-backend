@extends('templates.login')

@section('content')

<div class="form-top">
    <div class="form-top-left">
        <h3>Crie uma nova senha</h3>
        <p>Confirme seu email e escreva sua nova senha abaixo:</p>
    </div>
    <div class="form-top-right">
        <i class="fa fa-lock"></i>
    </div>
</div>
<div class="form-bottom">
    @include('includes.errors')

    <form role="form" action="{{ action('Auth\PasswordController@postReset') }}" method="post" class="login-form">

        {!! csrf_field() !!}

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label class="sr-only" for="email">Email</label>
            <input type="text" name="email" placeholder="Email..." class="form-email form-control" id="email" value="{{ old('email') }}">
        </div>

        <div class="form-group">
            <label class="sr-only" for="password">Senha</label>
            <input type="password" name="password" placeholder="Senha..." class="form-password form-control" id="password">
        </div>

        <div class="form-group">
            <label class="sr-only" for="password_confirmation">Repita a senha:</label>
            <input type="password" name="password_confirmation" placeholder="Repita a senha..." class="form-password form-control" id="password_confirmation">
        </div>

        <button type="submit" class="btn">Criar nova senha</button>
    </form>
</div>

@endsection