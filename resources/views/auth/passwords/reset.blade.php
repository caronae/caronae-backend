@extends('templates.login')

@section('content')
<div class="form-top">
    <div class="form-top-left">
        <h3>Redefinir senha</h3>
        <p>Digite a nova senha para continuar:</p>
    </div>
    <div class="form-top-right">
        <i class="fa fa-lock"></i>
    </div>
</div>

<div class="form-bottom">
    @include('includes.errors')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form class="login-form" role="form" method="POST" action="{{ route('password.request') }}">
        {{ csrf_field() }}
        
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label for="email" class="sr-only">E-mail</label>
            <input id="email" type="email" placeholder="E-mail" class="form-email form-control" name="email" value="{{ $email or old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password" class="sr-only">E-mail</label>
            <input id="password" type="password" placeholder="Senha" class="form-password form-control" name="password" required>
        </div>

        <div class="form-group">
            <label for="password-confirm" class="sr-only">E-mail</label>
            <input id="password-confirm" type="password" placeholder="Confirmar senha" class="form-password form-control" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn">
            Redefinir senha
        </button>
    </form>
</div>

@endsection