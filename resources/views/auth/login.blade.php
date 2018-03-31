@extends('templates.login')

@section('content')
<div class="form-top">
    <div class="form-top-left">
        <h3>Área administrativa</h3>
        <p>Digite o seu email e senha para acessar:</p>
    </div>
    <div class="form-top-right">
        <i class="fa fa-lock"></i>
    </div>
</div>
<div class="form-bottom">
    @include('includes.errors')
    <form role="form" action="" method="post" class="login-form">
        {!! csrf_field() !!}
        <div class="form-group">
            <label class="sr-only" for="email">Email</label>
            <input type="text" name="email" placeholder="Email" class="form-email form-control" id="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-group">
            <label class="sr-only" for="password">Senha</label>
            <input type="password" name="password" placeholder="Senha" class="form-password form-control" id="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <p class="forgot-password-link">
        <a href="{{ route('password.request') }}">Esqueci minha senha</a>
    </p>
</div>
@endsection